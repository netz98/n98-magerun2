<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use InvalidArgumentException;
use Magento\Framework\Exception\FileSystemException;
use N98\Magento\Command\Database\Compressor\Compressor;
use N98\Util\Console\Enabler;
use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\Exec;
use N98\Util\OperatingSystem;
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
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Tables to exclude entirely from the dump (including structure)'
            )
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
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
            ->addOption(
                'mydumper',
                null,
                InputOption::VALUE_NONE,
                'Use mydumper instead of mysqldump for potentially faster dumps'
            )
            ->addOption(
                'views',
                null,
                InputOption::VALUE_NONE,
                'Explicitly include views in the dump. Views are included by default if not otherwise excluded.'
            )
            ->addOption(
                'no-views',
                null,
                InputOption::VALUE_NONE,
                'Exclude all views from the dump.'
            )
            ->setDescription('Dumps database with mysqldump cli client');

        $help = <<<HELP
Dumps configured magento database with `mysqldump` or `mydumper`. You must have installed
the MySQL client tools.

On debian systems run `apt-get install mysql-client` or `apt-get install mydumper` to do that.

The command reads app/etc/env.php to find the correct settings.

See it in action: http://youtu.be/ttjZHY6vThs

- If you like to prepend a timestamp to the dump name the --add-time option
  can be used.

- The command comes with a compression function. Add i.e. `--compression=gz`
  to dump directly in gzip compressed file.

<comment>View Handling</comment>
 By default, views are included in the dump.
 --views: This option is mostly for clarity or to counteract a very broad exclusion pattern
          if you want to ensure views are included.
 --no-views: Use this option to exclude all views from the dump. If --no-views is used,
             views will be excluded even if they match patterns in --include or are
             not matched by --strip or --exclude patterns.

HELP;
        $this->setHelp($help);
    }

    /**
     * Prefixes a table/view name if it doesn't already have the prefix.
     *
     * @param string $name The table or view name.
     * @param string $prefix The database prefix.
     * @return string The prefixed name.
     */
    private function prefixTableIfNeeded($name, $prefix)
    {
        if (!empty($prefix) && strpos($name, $prefix) !== 0) {
            return $prefix . $name;
        }
        return $name;
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

    public function getHelp(): string
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
        if ($input->getOption('mydumper') !== false) {
            // Check if mydumper is installed
            exec('which mydumper 2>/dev/null', $cmdOutput, $returnVal);
            if ($returnVal !== 0) {
                $output->writeln(
                    '<warning>mydumper not found. Falling back to mysqldump. To use mydumper, install it first.</warning>'
                );
            } else {
                return $this->createMydumperExecs($input, $output);
            }
        }

        $database = $this->getDatabaseHelper();
        $execs = new Execs($database->getMysqlDumpBinary());
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
        $allViews = $database->getViews(); // Assumes getViews() returns unprefixed names
        $allActualTables = $database->getTables(true); // Assumes getTables(true) returns unprefixed names
        $dbPrefix = $this->dbSettings['prefix'];
        $dbName = $this->dbSettings['dbname'];

        $mysqlClientToolConnectionString = $database->getMysqlClientToolConnectionString();

        $excludeTablesUserInput = $this->excludeTables($input, $output); // Unprefixed
        $includeTablesUserInput = $this->includeTables($input, $output); // Unprefixed
        $stripTablesUserInput = $this->stripTables($input, $output);     // Unprefixed
        $stripTablesUserInput = array_diff($stripTablesUserInput, $excludeTablesUserInput);

        // Filter out any tables that should be excluded entirely
        // This ensures tables matching exclude patterns (like admin_*) are not included in structure dump
        $stripTablesUserInput = array_diff($stripTablesUserInput, $excludeTablesUserInput);

        // Structure dump part (for stripped tables and included tables)
        $tablesForStructureDump = array_merge($stripTablesUserInput, $includeTablesUserInput);
        $tablesForStructureDump = array_unique($tablesForStructureDump);

        if ($input->getOption('no-views')) {
            $tablesForStructureDump = array_intersect($tablesForStructureDump, $allActualTables);
        }
        $prefixedTablesForStructureDump = [];
        foreach ($tablesForStructureDump as $table) {
            $prefixedTablesForStructureDump[] = $this->prefixTableIfNeeded($table, $dbPrefix);
        }
        if ($prefixedTablesForStructureDump) {
            $execs->add(
                '--no-data ' . $mysqlClientToolConnectionString .
                ' ' . implode(' ', $prefixedTablesForStructureDump) . $this->postDumpPipeCommands($input)
            );
        }

        // Main dump part (data and remaining structures)
        $ignoreTableList = array_merge($excludeTablesUserInput, $stripTablesUserInput);

        if ($input->getOption('no-views')) {
            // If --no-views, add all views to the ignore list for the main dump
            $ignoreTableList = array_merge($ignoreTableList, $allViews);
        }
        $ignoreTableList = array_unique($ignoreTableList);

        $ignore = '';
        // exclude tables from dump
        if (count($ignoreTableList) > 0) {
            foreach ($ignoreTableList as $ignoreItem) {
                $prefixedIgnoreItem = $this->prefixTableIfNeeded($ignoreItem, $dbPrefix);
                $ignore .= '--ignore-table=' . $dbName . '.' . $prefixedIgnoreItem . ' ';
            }
        }
        $execs->add(
            rtrim($ignore) // Use rtrim to remove trailing space if any
            . ' ' . $mysqlClientToolConnectionString
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
    private function includeTables(InputInterface $input, OutputInterface $output)
    {
        $includeOption = $input->getOption('include');
        if (!$includeOption) {
            return [];
        }

        if (is_array($includeOption)) {
            $includeOption = implode(' ', $includeOption);
        }

        $includeTables = $this->resolveDatabaseTables($includeOption);

        if ($includeTables && $this->nonCommandOutput($input)) {
            $output->writeln(
                sprintf('<comment>Included: <info>%s</info></comment>', implode(' ', $includeTables))
            );
        }

        return $includeTables;
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
            $includeOption = $input->getOption('include');
            if (is_array($includeOption)) {
                $includeOption = implode(' ', $includeOption);
            }
            $includeTables = $this->resolveDatabaseTables($includeOption);
            $excludeTables = array_diff($database->getTables(), $includeTables);
        }

        if ($input->getOption('exclude')) {
            $excludeOption = $input->getOption('exclude');
            if (is_array($excludeOption)) {
                $excludeOption = implode(' ', $excludeOption);
            }
            $excludeTables = array_merge($excludeTables, $this->resolveDatabaseTables($excludeOption));
            if (isset($includeTables)) { // only needed when also "include" was given
                asort($excludeTables);
                $excludeTables = array_unique($excludeTables);
                $excludeTables = array_diff($excludeTables, $includeTables);
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

        if ($fileName = $input->getArgument('filename')) {
            // Expand tilde
            if (strpos($fileName, '~') === 0 && ($home = OperatingSystem::getHomeDir())) {
                if ($fileName === '~') {
                    $fileName = $home;
                } elseif (strpos($fileName, '~/') === 0) {
                    $fileName = $home . substr($fileName, 1);
                }
            }
        }

        if (
            (
                $fileName === null
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

    /**
     * Create mydumper execution commands
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Execs
     */
    private function createMydumperExecs(InputInterface $input, OutputInterface $output)
    {
        /* @var $database DatabaseHelper */
        $database = $this->getDatabaseHelper(); // Ensure helper is available
        $dbPrefix = $this->dbSettings['prefix'];

        $execs = new Execs('mydumper');
        $execs->setCompression($input->getOption('compression'), $input);

        // Get output directory from filename
        $outputDir = dirname($this->getFileName($input, $output, $execs->getCompressor()));
        $execs->addOptions('--outputdir=' . escapeshellarg($outputDir));

        // Database connection options
        $execs->addOptions(sprintf(
            '--host=%s --user=%s --password=%s --database=%s',
            escapeshellarg($this->dbSettings['host']),
            escapeshellarg($this->dbSettings['username']),
            escapeshellarg($this->dbSettings['password']),
            escapeshellarg($this->dbSettings['dbname'])
        ));

        if (!$input->getOption('no-single-transaction')) {
            $execs->addOptions('--trx-consistency-only');
        }

        if ($input->getOption('human-readable')) {
            $execs->addOptions('--rows=1');
        }

        // For git-friendly output, use statement-per-row and long-query-guard
        if ($input->getOption('git-friendly')) {
            $execs->addOptions('--rows=1 --long-query-guard=0 --complete-insert');
            // Add post-processing to format output similar to mysqldump git-friendly
            $execs->addOptions(' | sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\'');
        }

        if ($input->getOption('add-routines')) {
            $execs->addOptions('--routines');
        }

        // Handle excluded and stripped tables
        $excludeTablesUserInput = $this->excludeTables($input, $output); // Unprefixed
        $stripTablesUserInput = array_diff($this->stripTables($input, $output), $excludeTablesUserInput); // Unprefixed

        $ignoreOptions = [];
        $noDataOptions = [];

        foreach ($excludeTablesUserInput as $table) {
            $ignoreOptions[] = '--ignore-table=' . escapeshellarg($this->prefixTableIfNeeded($table, $dbPrefix));
        }

        foreach ($stripTablesUserInput as $table) {
            // For mydumper, stripping data from a table that is also a view doesn't make sense
            // as views don't store data directly. We only care about actual tables for --no-data.
            // However, if --no-views is active, views will be added to ignore-table list later.
            $allActualTables = $database->getTables(true);
            if (in_array($table, $allActualTables, true)) {
                $noDataOptions[] = '--no-data=' . escapeshellarg($this->prefixTableIfNeeded($table, $dbPrefix));
            }
        }

        if ($input->getOption('no-views')) {
            $allViews = $database->getViews(); // Unprefixed
            foreach ($allViews as $view) {
                // Add to ignore list, ensuring no duplicates if already excluded by other means
                $prefixedViewName = $this->prefixTableIfNeeded($view, $dbPrefix);
                $ignoreOption = '--ignore-table=' . escapeshellarg($prefixedViewName);
                if (!in_array($ignoreOption, $ignoreOptions, true)) {
                    $ignoreOptions[] = $ignoreOption;
                }
            }
        }

        if ($ignoreOptions) {
            $execs->addOptions(implode(' ', array_unique($ignoreOptions)));
        }
        if ($noDataOptions) {
            $execs->addOptions(implode(' ', array_unique($noDataOptions)));
        }

        return $execs;
    }
}
