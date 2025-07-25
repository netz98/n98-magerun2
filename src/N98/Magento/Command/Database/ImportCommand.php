<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use Exception;
use InvalidArgumentException;
use N98\Magento\Command\Database\Compressor\AbstractCompressor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Class ImportCommand
 * @package N98\Magento\Command\Database
 */
class ImportCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:import')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Dump filename')
            ->addOption('compression', 'c', InputOption::VALUE_OPTIONAL, 'The compression of the specified file')
            ->addOption('zstd-level', null, InputOption::VALUE_OPTIONAL, '', 10)
            ->addOption('zstd-extra-args', null, InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('only-command', null, InputOption::VALUE_NONE, 'Print only mysql command. Do not execute')
            ->addOption('only-if-empty', null, InputOption::VALUE_NONE, 'Imports only if database is empty')
            ->addOption(
                'optimize',
                null,
                InputOption::VALUE_NONE,
                'Convert verbose INSERTs to short ones before import (not working with compression)'
            )
            ->addOption('drop', null, InputOption::VALUE_NONE, 'Drop and recreate database before import')
            ->addOption('drop-tables', null, InputOption::VALUE_NONE, 'Drop tables before import')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Continue even if an SQL error occurs')
            ->addOption('skip-authorization-entry-creation', null, InputOption::VALUE_NONE, 'Do not create authorization rule/role entries if they are missing')
            ->setDescription('Imports database with mysql cli client according to database defined in env.php');

        $help = <<<HELP
Imports an SQL file with mysql cli client into current configured database.

You need to have MySQL client tools installed on your system.
HELP;
        $help .= "\n" . $this->getCompressionHelp();

        $this->setHelp($help);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec');
    }

    /**
     * Optimize a dump by converting single INSERTs per line to INSERTs with multiple lines
     * as well as wrapping everything into one transaction.
     * @param $fileName
     * @return string temporary filename
     */
    protected function optimize($fileName)
    {
        $in = fopen($fileName, 'r');
        $result = tempnam(sys_get_temp_dir(), 'dump') . '.sql';
        $out = fopen($result, 'w');
        fwrite($out, 'SET autocommit=0;' . "\n");
        $currentTable = '';
        $maxlen = 8 * 1024 * 1024; // 8 MB
        $len = 0;
        while ($line = fgets($in)) {
            if (strtolower(substr($line, 0, 11)) == 'insert into') {
                preg_match('/^insert into `(.*)` \([^)]*\) values (.*);/i', $line, $m);
                if (count($m) < 3) { // fallback for very long lines or other cases where the preg_match fails
                    if ($currentTable != '') {
                        fwrite($out, ";\n");
                    }
                    fwrite($out, $line);
                    $currentTable = '';
                    continue;
                }
                $table = $m[1];
                $values = $m[2];
                if ($table != $currentTable || ($len > $maxlen - 1000)) {
                    if ($currentTable != '') {
                        fwrite($out, ";\n");
                    }
                    $currentTable = $table;
                    $insert = 'INSERT INTO `' . $table . '` VALUES ' . $values;
                    fwrite($out, $insert);
                    $len = strlen($insert);
                } else {
                    fwrite($out, ',' . $values);
                    $len += strlen($values) + 1;
                }
            } else {
                if ($currentTable != '') {
                    fwrite($out, ";\n");
                    $currentTable = '';
                }
                fwrite($out, $line);
            }
        }
        fwrite($out, ";\n");
        fwrite($out, 'COMMIT;' . "\n");
        fclose($in);
        fclose($out);
        return $result;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);
        $this->writeSection($output, 'Import MySQL Database');
        $dbHelper = $this->getHelper('database');

        $fileName = $this->checkFilename($input);

        if ($input->getOption('compression')) {
            $compression = $input->getOption('compression');
        } else {
            $compression = AbstractCompressor::tryGetCompressionType($fileName);

            if ($compression === null) {
                $output->writeln(
                    '<comment>No compression detected. Using default compression: <info>none</info></comment>',
                );

                $compression = 'none';
            }
        }

        $compressor = AbstractCompressor::create($compression, $input);

        $mysqlBinary = $dbHelper->getMysqlBinary();
        $exec = $mysqlBinary . ' ';
        if ($input->getOption('force')) {
            $exec = $mysqlBinary . ' --force ';
        }

        // create import command
        $exec = $compressor->getDecompressingCommand(
            $exec . $dbHelper->getMysqlClientToolConnectionString(),
            $fileName
        );

        if ($input->getOption('only-command')) {
            $output->writeln($exec);

            return Command::SUCCESS;
        }

        if ($input->getOption('only-if-empty')
            && count($dbHelper->getTables()) > 0) {
            $output->writeln('<comment>Skip import. Database is not empty</comment>');

            return Command::SUCCESS;
        }

        if ($input->getOption('optimize')) {
            if ($input->getOption('compression')) {
                throw new Exception('Options --compression and --optimize are not compatible');
            }
            $output->writeln('<comment>Optimizing <info>' . $fileName . '</info> to temporary file');
            $fileName = $this->optimize($fileName);
        }

        if ($input->getOption('drop')) {
            $dbHelper->dropDatabase($output);
            $dbHelper->createDatabase($output);
        }

        if ($input->getOption('drop-tables')) {
            $dbHelper->dropTables($output);
        }

        $success = $this->doImport($output, $fileName, $exec);

        if ($input->getOption('optimize')) {
            unlink($fileName);
        }

        if (!$input->getOption('skip-authorization-entry-creation')) {
            $this->getApplication()->run(new StringInput('db:add-default-authorization-entries'), $output);
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @param InputInterface $input
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function checkFilename(InputInterface $input)
    {
        $fileName = $input->getArgument('filename');

        if ($fileName === null) {
            throw new InvalidArgumentException('Please provide a file name');
        }

        if (!file_exists($fileName)) {
            throw new InvalidArgumentException('File does not exist');
        }

        return $fileName;
    }

    /**
     * @param OutputInterface $output
     * @param string $fileName
     * @param string $exec
     *
     * @return bool
     */
    protected function doImport(OutputInterface $output, $fileName, $exec): bool
    {
        $success = true;

        $sttyMode = null;
        if (Terminal::hasSttyAvailable()) {
            $sttyMode = exec('stty -g');
        }

        $returnValue = null;
        $commandOutput = null;
        $output->writeln(
            '<comment>Importing SQL dump <info>' . $fileName . '</info> to database <info>'
            . $this->dbSettings['dbname'] . '</info>'
        );
        exec($exec, $commandOutput, $returnValue);
        if ($returnValue != 0) {
            $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
            $success = false;
        }
        $output->writeln('<info>Finished</info>');

        if (!is_null($sttyMode)) {
            // Restore stty mode because 'pv' breaks it in some cases
            exec(sprintf('stty %s', $sttyMode));
        }

        return $success;
    }
}
