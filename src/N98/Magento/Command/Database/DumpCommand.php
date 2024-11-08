<?php

namespace N98\Magento\Command\Database;

use InvalidArgumentException;
use Magento\Framework\Exception\FileSystemException;
use N98\Magento\Command\Database\Compressor\Compressor;
use N98\Util\Console\Enabler;
use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\Exec;
use N98\Util\VerifyOrDie;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class DumpCommand
 * @package N98\Magento\Command\Database
 */
class DumpCommand extends AbstractDatabaseCommand
{
    /**
     * @var array
     */
    protected $tableDefinitions = null;

    /**
     * @var array
     */
    protected $commandConfig = null;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:dump')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Dump filename')
            ->addOption('zstd-level', null, InputOption::VALUE_OPTIONAL, 'Set the level of compression the higher the smaller the result', 10)
            ->addOption('zstd-extra-args', null, InputOption::VALUE_OPTIONAL, 'Set custom extra options that zstd supports', '')
            ->addOption(
                'add-time',
                't',
                InputOption::VALUE_OPTIONAL,
                'Append or prepend a timestamp to filename if a filename is provided. ' .
                'Possible values are "suffix", "prefix" or "no".',
                'no'
            )
            ->addOption(
                'compression',
                'c',
                InputOption::VALUE_REQUIRED,
                'Compress the dump file using one of the supported algorithms'
            )
            ->addOption(
                'only-command',
                null,
                InputOption::VALUE_NONE,
                'Print only mysqldump command. Do not execute'
            )
            ->addOption(
                'print-only-filename',
                null,
                InputOption::VALUE_NONE,
                'Execute and prints no output except the dump filename'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do everything but the actual dump'
            )
            ->addOption(
                'set-gtid-purged-off',
                null,
                InputOption::VALUE_NONE,
                'add --set-gtid-purged=OFF'
            )
            ->addOption(
                'no-single-transaction',
                null,
                InputOption::VALUE_NONE,
                'Do not use single-transaction (not recommended, this is blocking)'
            )
            ->addOption(
                'human-readable',
                null,
                InputOption::VALUE_NONE,
                'Use a single insert with column names per row. Useful to track database differences. Use db:import ' .
                '--optimize for speeding up the import.'
            )
            ->addOption(
                'git-friendly',
                null,
                InputOption::VALUE_NONE,
                'Use one insert statement, but with line breaks instead of separate insert statements. Similar to --human-readable, but you wont need to use --optimize to speed up the import.'
            )
            ->addOption(
                'add-routines',
                null,
                InputOption::VALUE_NONE,
                'Include stored routines in dump (procedures & functions)'
            )
            ->addOption(
                'no-tablespaces',
                null,
                InputOption::VALUE_NONE,
                'Use this option if you want to create a dump without having the PROCESS privilege'
            )
            ->addOption(
                'stdout',
                null,
                InputOption::VALUE_NONE,
                'Dump to stdout'
            )
            ->addOption(
                'strip',
                's',
                InputOption::VALUE_OPTIONAL,
                'Tables to strip (dump only structure of those tables)'
            )
            ->addOption(
                'exclude',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Tables to exclude entirely from the dump (including structure)'
            )
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Tables to include entirely in the dump (including structure)'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Do not prompt if all options are defined'
            )
            ->addOption(
                'keep-column-statistics',
                null,
                InputOption::VALUE_NONE,
                'Keeps the Column Statistics table in SQL dump'
            )
            ->addOption(
                'keep-definer',
                null,
                InputOption::VALUE_NONE,
                'Do not replace DEFINER in dump with CURRENT_USER'
            )
            ->setDescription('Dumps database with mysqldump cli client');

        $help = <<<HELP
Dumps configured magento database with `mysqldump`. You must have installed
the MySQL client tools.

On debian systems run `apt-get install mysql-client` to do that.

The command reads app/etc/env.php to find the correct settings.

See it in action: http://youtu.be/ttjZHY6vThs

- If you like to prepend a timestamp to the dump name the --add-time option
  can be used.

- The command comes with a compression function. Add i.e. `--compression=gz`
  to dump directly in gzip compressed file.

HELP;
        $this->setHelp($help);
    }

    /**
     * @return array
     *
     * @deprecated Use database helper
     */
    private function getTableDefinitions()
    {
        $this->commandConfig = $this->getCommandConfig();

        if ($this->tableDefinitions === null) {
            /* @var $dbHelper DatabaseHelper */
            $dbHelper = $this->getHelper('database');

            $this->tableDefinitions = $dbHelper->getTableDefinitions($this->commandConfig);
        }

        return $this->tableDefinitions;
    }

    /**
     * Generate help for table definitions
     *
     * @return string
     */
    public function getTableDefinitionHelp()
    {
        $messages = PHP_EOL;
        $this->commandConfig = $this->getCommandConfig();
        $messages .= <<<HELP
<comment>Strip option</comment>
 If you like to skip data of some tables you can use the --strip option.
 The strip option creates only the structure of the defined tables and
 forces `mysqldump` to skip the data.

 Separate each table to strip by a space.
 You can use wildcards like * and ? in the table names to strip multiple
 tables. In addition, you can specify pre-defined table groups, that start
 with an @ symbol.

 Example: "dataflow_batch_export unimportant_module_* @log"

    $ n98-magerun2.phar db:dump --strip="@stripped"

<comment>Available Table Groups</comment>

HELP;

        $definitions = $this->getTableDefinitions();
        $list = [];
        $maxNameLen = 0;
        foreach ($definitions as $id => $definition) {
            $name = '@' . $id;
            $description = isset($definition['description']) ? $definition['description'] . '.' : '';
            $nameLen = strlen($name);
            if ($nameLen > $maxNameLen) {
                $maxNameLen = $nameLen;
            }
            $list[] = [$name, $description];
        }

        $decrSize = 78 - $maxNameLen - 3;

        foreach ($list as $entry) {
            list($name, $description) = $entry;
            $delta = max(0, $maxNameLen - strlen($name));
            $spacer = $delta ? str_repeat(' ', $delta) : '';
            $buffer = wordwrap($description, $decrSize);
            $buffer = strtr($buffer, ["\n" => "\n" . str_repeat(' ', 3 + $maxNameLen)]);
            $messages .= sprintf(" <info>%s</info>%s  %s\n", $name, $spacer, $buffer);
        }

        $messages .= <<<HELP

Extended: https://github.com/netz98/n98-magerun/wiki/Stripped-Database-Dumps
HELP;

        return $messages;
    }

    public function getHelp()
    {
        return
            parent::getHelp() . PHP_EOL
            . $this->getCompressionHelp() . PHP_EOL
            . $this->getTableDefinitionHelp();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // communicate early what is required for this command to run (is enabled)
        $enabler = new Enabler($this);
        $enabler->functionExists('exec');
        $enabler->functionExists('passthru');
        $enabler->operatingSystemIsNotWindows();

        $this->detectDbSettings($output);

        if ($this->nonCommandOutput($input)) {
            $this->writeSection($output, 'Dump MySQL Database');
        }

        $execs = $this->createExecs($input, $output);

        $success = $this->runExecs($execs, $input, $output);

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Execs
     * @throws FileSystemException
     */
    private function createExecs(InputInterface $input, OutputInterface $output)
    {
        $execs = new Execs('mysqldump');
        $execs->setCompression($input->getOption('compression'), $input);
        $execs->setFileName($this->getFileName($input, $output, $execs->getCompressor()));

        if (!$input->getOption('no-single-transaction')) {
            $execs->addOptions('--single-transaction --quick');
        }

        if ($input->getOption('human-readable')) {
            $execs->addOptions('--complete-insert --skip-extended-insert ');
        }

        if ($input->getOption('set-gtid-purged-off')) {
            $execs->addOptions('--set-gtid-purged=OFF ');
        }

        if ($input->getOption('add-routines')) {
            $execs->addOptions('--routines ');
        }

        if ($input->getOption('no-tablespaces')) {
            $execs->addOptions('--no-tablespaces ');
        }

        if ($this->checkColumnStatistics()) {
            if ($input->getOption('keep-column-statistics')) {
                $execs->addOptions('--column-statistics=1 ');
            } else {
                $execs->addOptions('--column-statistics=0 ');
            }
        }

        $postDumpGitFriendlyPipeCommands = '';
        if ($input->getOption('git-friendly')) {
            $postDumpGitFriendlyPipeCommands = ' | sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\'';
        }

        /* @var $database DatabaseHelper */
        $database = $this->getDatabaseHelper();

        $mysqlClientToolConnectionString = $database->getMysqlClientToolConnectionString();

        $excludeTables = $this->excludeTables($input, $output);
        $stripTables = array_diff($this->stripTables($input, $output), $excludeTables);
        if ($stripTables) {
            // dump structure for strip-tables
            $execs->add(
                '--no-data ' . $mysqlClientToolConnectionString .
                ' ' . implode(' ', $stripTables) . $this->postDumpPipeCommands($input)
            );
        }

        // dump data for all other tables
        $ignore = '';
        foreach (array_merge($excludeTables, $stripTables) as $ignoreTable) {
            $ignore .= '--ignore-table=' . $this->dbSettings['dbname'] . '.' . $ignoreTable . ' ';
        }

        $execs->add(
            $ignore
            . $mysqlClientToolConnectionString
            . $postDumpGitFriendlyPipeCommands
            . $this->postDumpPipeCommands($input)
        );

        return $execs;
    }

    /**
     * @param Execs $execs
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    private function runExecs(Execs $execs, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('only-command') && !$input->getOption('print-only-filename')) {
            foreach ($execs->getCommands() as $command) {
                $output->writeln($command);
            }
        } else {
            if ($this->nonCommandOutput($input)) {
                $output->writeln(
                    '<comment>Start dumping database <info>' . $this->dbSettings['dbname'] .
                    '</info> to file <info>' . $execs->getFileName() . '</info>'
                );
            }

            $commands = $input->getOption('dry-run') ? [] : $execs->getCommands();

            foreach ($commands as $command) {
                if (!$this->runExec($command, $input, $output)) {
                    return false;
                }
            }

            if (!$input->getOption('stdout') && !$input->getOption('print-only-filename')) {
                $output->writeln('<info>Finished</info>');
            }
        }

        if ($input->getOption('print-only-filename')) {
            $output->writeln($execs->getFileName());
        }

        return true;
    }

    /**
     * @param string $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    private function runExec($command, InputInterface $input, OutputInterface $output)
    {
        $commandOutput = '';

        if ($input->getOption('stdout')) {
            passthru($command, $returnCode);
        } else {
            Exec::run($command, $commandOutput, $returnCode);
        }

        if ($returnCode > 0) {
            $output->writeln('<error>' . $commandOutput . '</error>');
            $output->writeln('<error>Return Code: ' . $returnCode . '. ABORTED.</error>');

            return false;
        }

        return true;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws FileSystemException
     */
    private function stripTables(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('strip')) {
            return [];
        }

        $stripTables = $this->resolveDatabaseTables($input->getOption('strip'));

        if ($this->nonCommandOutput($input)) {
            $output->writeln(
                sprintf('<comment>No-data export for: <info>%s</info></comment>', implode(' ', $stripTables))
            );
        }

        return $stripTables;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws FileSystemException
     */
    private function excludeTables(InputInterface $input, OutputInterface $output)
    {
        $excludeTables = [];

        if ($input->getOption('include')) {
            $database = $this->getDatabaseHelper();
            $includeTables = $this->resolveDatabaseTables($input->getOption('include'));
            $excludeTables = array_diff($database->getTables(), $includeTables);
        }

        if ($input->getOption('exclude')) {
            $excludeTables = array_merge($excludeTables, $this->resolveDatabaseTables($input->getOption('exclude')));
            if (isset($includeTables)) { // only needed when also "include" was given
                asort($excludeTables);
                $excludeTables = array_unique($excludeTables);
            }
        }

        if ($excludeTables && $this->nonCommandOutput($input)) {
            $output->writeln(
                sprintf('<comment>Excluded: <info>%s</info></comment>', implode(' ', $excludeTables))
            );
        }

        return $excludeTables;
    }

    /**
     * @param string $list space separated list of tables
     * @return array
     * @throws FileSystemException
     */
    private function resolveDatabaseTables($list)
    {
        $database = $this->getDatabaseHelper();

        return $database->resolveTables(
            explode(' ', $list),
            $database->getTableDefinitions($this->getCommandConfig())
        );
    }

    /**
     * Commands which filter mysql data. Piped to mysqldump command
     *
     * @param InputInterface $input
     * @return string
     */
    protected function postDumpPipeCommands(InputInterface $input)
    {
        return $input->getOption('keep-definer')
            ? ''
            : ' | LANG=C LC_CTYPE=C LC_ALL=C sed -E '
              . escapeshellarg('s/DEFINER[ ]*=[ ]*`[^`]+`@`[^`]+`/DEFINER=CURRENT_USER/g');
    }

    /**
     * Command which makes the dump git friendly. Piped to mysqldump command.
     *
     * @return string
     */
    protected function postDumpGitFriendlyPipeCommands()
    {
        return ' | sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\'';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Compressor $compressor
     *
     * @return string
     */
    protected function getFileName(InputInterface $input, OutputInterface $output, Compressor $compressor)
    {
        $nameExtension = '.sql';

        $optionAddTime = 'no';
        if ($input->getOption('add-time')) {
            $optionAddTime = $input->getOption('add-time');
        }

        list($namePrefix, $nameSuffix) = $this->getFileNamePrefixSuffix($optionAddTime);

        if (
            (
                ($fileName = $input->getArgument('filename')) === null
                || ($isDir = is_dir($fileName))
            )
            && !$input->getOption('stdout')
        ) {
            $defaultName = VerifyOrDie::filename(
                $namePrefix . $this->dbSettings['dbname'] . $nameSuffix . $nameExtension
            );
            if (isset($isDir) && $isDir) {
                $defaultName = rtrim($fileName, '/') . '/' . $defaultName;
            }
            if (!$input->getOption('force')) {
                $question = new Question(
                    '<question>Filename for SQL dump:</question> [<comment>' . $defaultName . '</comment>]',
                    $defaultName
                );

                /** @var QuestionHelper $questionHelper */
                $questionHelper = $this->getHelper('question');
                $fileName = $questionHelper->ask(
                    $input,
                    $output,
                    $question
                );
            } else {
                $fileName = $defaultName;
            }
        } elseif ($optionAddTime && $fileName !== null) {
            $pathParts = pathinfo($fileName);

            $fileName = ($pathParts['dirname'] === '.' ? '' : $pathParts['dirname'] . '/')
                . $namePrefix
                . (isset($pathParts['filename']) ? $pathParts['filename'] : '')
                . $nameSuffix
                . (isset($pathParts['extension']) ? ('.' . $pathParts['extension']) : '');
        }

        $fileName = $compressor->getFileName($fileName);

        return $fileName;
    }

    /**
     * @param null|bool|string $optionAddTime [optional] true for default "suffix", other string values: "prefix", "no"
     * @return array
     */
    private function getFileNamePrefixSuffix($optionAddTime = null)
    {
        $namePrefix = '';
        $nameSuffix = '';
        if ($optionAddTime === null) {
            return [$namePrefix, $nameSuffix];
        }

        $timeStamp = date('Y-m-d_His');

        if (in_array($optionAddTime, ['suffix', true], true)) {
            $nameSuffix = '_' . $timeStamp;
        } elseif ($optionAddTime === 'prefix') {
            $namePrefix = $timeStamp . '_';
        } elseif ($optionAddTime !== 'no') {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid --add-time value %s, possible values are none (for) "suffix", "prefix" or "no"',
                    var_export($optionAddTime, true)
                )
            );
        }

        return [$namePrefix, $nameSuffix];
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    private function nonCommandOutput(InputInterface $input)
    {
        return
            !$input->getOption('stdout')
            && !$input->getOption('only-command')
            && !$input->getOption('print-only-filename');
    }

    /**
     * Checks if 'column statistics' are present in the current MySQL distribution
     *
     * @return bool
     */
    private function checkColumnStatistics()
    {
        Exec::run('mysqldump --help | grep -c column-statistics || true', $output, $returnCode);

        if ($output > 0) {
            return true;
        }

        return false;
    }
}
