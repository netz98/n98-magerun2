<?php

namespace N98\Magento\Command\Database;

use N98\Magento\Command\Database\Compressor\AbstractCompressor;
use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this
            ->setName('db:dump')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Dump filename')
            ->addOption(
                'add-time',
                't',
                InputOption::VALUE_OPTIONAL,
                'Adds time to filename (only if filename was not provided)'
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
                'Print only mysqldump command. 
                Do not execute'
            )
            ->addOption(
                'print-only-filename',
                null,
                InputOption::VALUE_NONE,
                'Execute and prints no output except the dump filename'
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'do everything but the dump')
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
                'Use a single insert with column names per row. Useful to track database differences. Use ' .
                'db:import --optimize for speeding up the import.'
            )
            ->addOption(
                'add-routines',
                null,
                InputOption::VALUE_NONE,
                'Include stored routines in dump (procedures & functions)'
            )
            ->addOption('stdout', null, InputOption::VALUE_NONE, 'Dump to stdout')
            ->addOption(
                'strip',
                's',
                InputOption::VALUE_OPTIONAL,
                'Tables to strip (dump only structure of those tables)'
            )
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not prompt if all options are defined')
            ->setDescription('Dumps database with mysqldump cli client according to informations from local.xml');

        $help = <<<HELP
Dumps configured magento database with `mysqldump`.
You must have installed the MySQL client tools.

On debian systems run `apt-get install mysql-client` to do that.

The command reads app/etc/local.xml to find the correct settings.
If you like to skip data of some tables you can use the --strip option.
The strip option creates only the structure of the defined tables and
forces `mysqldump` to skip the data.

Dumps your database and excludes some tables. This is useful i.e. for development.

Separate each table to strip by a space.
You can use wildcards like * and ? in the table names to strip multiple tables.
In addition you can specify pre-defined table groups, that start with an @
Example: "dataflow_batch_export unimportant_module_* @log

   $ n98-magerun.phar db:dump --strip="@stripped"

Available Table Groups:

* @log Log tables
* @dataflowtemp Temporary tables of the dataflow import/export tool
* @stripped Standard definition for a stripped dump (logs and dataflow)
* @sales Sales data (orders, invoices, creditmemos etc)
* @customers Customer data
* @trade Current trade data (customers and orders). You usally do not want those in developer systems.
* @development Removes logs and trade data so developers do not have to work with real customer data

Extended: https://github.com/netz98/n98-magerun/wiki/Stripped-Database-Dumps

See it in action: http://youtu.be/ttjZHY6vThs

- If you like to prepend a timestamp to the dump name the --add-time option can be used.

- The command comes with a compression function. Add i.e. `--compression=gz` to dump directly in
 gzip compressed file.

HELP;
        $this->setHelp($help);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec') && !OperatingSystem::isWindows();
    }

    /**
     * @return array
     *
     * @deprecated Use database helper
     * @throws RuntimeException
     */
    public function getTableDefinitions()
    {
        $this->commandConfig = $this->getCommandConfig();

        if (is_null($this->tableDefinitions)) {
            $this->tableDefinitions = array();
            if (isset($this->commandConfig['table-groups'])) {
                $tableGroups = $this->commandConfig['table-groups'];
                foreach ($tableGroups as $index => $definition) {
                    $description = isset($definition['description']) ? $definition['description'] : '';
                    if (!isset($definition['id'])) {
                        throw new RuntimeException('Invalid definition of table-groups (id missing) Index: ' . $index);
                    }
                    if (!isset($definition['id'])) {
                        throw new RuntimeException(
                            'Invalid definition of table-groups (tables missing) Id: ' . $definition['id']
                        );
                    }

                    $this->tableDefinitions[$definition['id']] = array(
                        'tables'      => $definition['tables'],
                        'description' => $description,
                    );
                }
            };
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
        $messages = array();
        $this->commandConfig = $this->getCommandConfig();
        $messages[] = '';
        $messages[] = '<comment>Strip option</comment>';
        $messages[] = ' Separate each table to strip by a space.';
        $messages[] = ' You can use wildcards like * and ? in the table names to strip multiple tables.';
        $messages[] = ' In addition you can specify pre-defined table groups, that start with an @';
        $messages[] = ' Example: "dataflow_batch_export unimportant_module_* @log';
        $messages[] = '';
        $messages[] = '<comment>Available Table Groups</comment>';

        $definitions = $this->getTableDefinitions();
        foreach ($definitions as $id => $definition) {
            $description = isset($definition['description']) ? $definition['description'] : '';
            /** @TODO:
             * Column-Wise formatting of the options, see InputDefinition::asText for code to pad by the max length,
             * but I do not like to copy and paste ..
             */
            $messages[] = ' <info>@' . $id . '</info> ' . $description;
        }

        return implode(PHP_EOL, $messages);
    }

    public function getHelp()
    {
        return parent::getHelp() . PHP_EOL
        . $this->getCompressionHelp() . PHP_EOL
        . $this->getTableDefinitionHelp();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        if ($this->nonCommandOutput($input)) {
            $this->writeSection($output, 'Dump MySQL Database');
        }

        $execs = $this->createExecs($input, $output);

        $this->runExecs($execs, $input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Execs
     */
    private function createExecs(InputInterface $input, OutputInterface $output)
    {
        $execs = new Execs('mysqldump');
        $execs->setCompression($input->getOption('compression'));
        $execs->setFileName($this->getFileName($input, $output, $execs->getCompressor()));

        if (!$input->getOption('no-single-transaction')) {
            $execs->addOptions('--single-transaction --quick');
        }

        if ($input->getOption('human-readable')) {
            $execs->addOptions('--complete-insert --skip-extended-insert ');
        }

        if ($input->getOption('add-routines')) {
            $execs->addOptions('--routines ');
        }

        $database = $this->getDatabaseHelper();
        $stripTables = $this->stripTables($input, $output);
        if ($stripTables) {
            // dump structure for strip-tables
            $execs->add(
                '--no-data ' . $database->getMysqlClientToolConnectionString() .
                ' ' . implode(' ', $stripTables) . $this->postDumpPipeCommands()
            );

            // dump data for all other tables
            $ignore = '';
            foreach ($stripTables as $stripTable) {
                $ignore .= '--ignore-table=' . $this->dbSettings['dbname'] . '.' . $stripTable . ' ';
            }
            $execs->add(
                $ignore . $database->getMysqlClientToolConnectionString() . $this->postDumpPipeCommands()
            );

            return $execs;
        } else {
            $execs->add(
                $database->getMysqlClientToolConnectionString() . $this->postDumpPipeCommands()
            );

            return $execs;
        }
    }

    /**
     * @param Execs $execs
     * @param InputInterface $input
     * @param OutputInterface $output
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

            $commands = $input->getOption('dry-run') ? array() : $execs->getCommands();

            foreach ($commands as $command) {
                if (!$this->runExec($command, $input, $output)) {
                    return;
                }
            }

            if (!$input->getOption('stdout') && !$input->getOption('print-only-filename')) {
                $output->writeln('<info>Finished</info>');
            }
        }

        if ($input->getOption('print-only-filename')) {
            $output->writeln($execs->getFileName());
        }
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
            passthru($command, $returnValue);
        } else {
            exec($command, $commandOutput, $returnValue);
        }

        if ($returnValue > 0) {
            $output->writeln([
                '<error>' . implode(PHP_EOL, $commandOutput) . '</error>',
                '<error>Return Code: ' . $returnValue . '. ABORTED.</error>',
            ]);
            return false;
        }
        return true;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return false|array
     */
    private function stripTables(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('strip')) {
            return false;
        }

        $stripTables = $this->getDatabaseHelper()->resolveTables(
            explode(' ', $input->getOption('strip')),
            $this->getTableDefinitions()
        );

        if ($this->nonCommandOutput($input)) {
            $output->writeln(
                '<comment>No-data export for: <info>' . implode(' ', $stripTables) . '</info></comment>'
            );
        }

        return $stripTables;
    }

    /**
     * Commands which filter mysql data. Piped to mysqldump command
     *
     * @return string
     */
    protected function postDumpPipeCommands()
    {
        return ' | sed -e ' . escapeshellarg('s/DEFINER[ ]*=[ ]*[^*]*\*/\*/');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AbstractCompressor $compressor
     * @return string
     */
    protected function getFileName(
        InputInterface $input,
        OutputInterface $output,
        AbstractCompressor $compressor
    ) {
        $namePrefix = '';
        $nameSuffix = '';
        $nameExtension = '.sql';

        if ($input->getOption('add-time') !== false) {
            $timeStamp = date('Y-m-d_His');

            if ($input->getOption('add-time') == 'suffix') {
                $nameSuffix = '_' . $timeStamp;
            } else {
                $namePrefix = $timeStamp . '_';
            }
        }

        if (
            (
                ($fileName = $input->getArgument('filename')) === null
                || ($isDir = is_dir($fileName))
            )
            && !$input->getOption('stdout')
        ) {
            $defaultName = $namePrefix . $this->dbSettings['dbname'] . $nameSuffix . $nameExtension;
            if (isset($isDir) && $isDir) {
                $defaultName = rtrim($fileName, '/') . '/' . $defaultName;
            }
            if (!$input->getOption('force')) {
                /** @var DialogHelper $dialog */
                $dialog = $this->getHelper('dialog');
                $fileName = $dialog->ask(
                    $output,
                    '<question>Filename for SQL dump:</question> [<comment>' . $defaultName . '</comment>]',
                    $defaultName
                );
            } else {
                $fileName = $defaultName;
            }
        } else {
            if ($input->getOption('add-time')) {
                $pathParts = pathinfo($fileName);
                $fileName = ($pathParts['dirname'] == '.' ? '' : $pathParts['dirname'] . '/') .
                    $namePrefix . $pathParts['filename'] . $nameSuffix . '.' . $pathParts['extension'];
            }
        }

        $fileName = $compressor->getFileName($fileName);

        return $fileName;
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
}
