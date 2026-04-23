<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PrepareUpgradeCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('setup:prepare-upgrade')
            ->addOption(
                'original-db',
                null,
                InputOption::VALUE_REQUIRED,
                'Original database name before upgrade (must be on same MySQL server)'
            )
            ->addOption(
                'output-file',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output SQL filename (default: upgrade-<timestamp>.sql)'
            )
            ->addOption(
                'no-data-diff',
                null,
                InputOption::VALUE_NONE,
                'Skip data comparison, only diff schema (DDL)'
            )
            ->addOption(
                'compare-extra-arg',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Additional raw arguments forwarded to mysqldbcompare (repeatable)'
            )
            ->setDescription('Run setup:upgrade on current env.php DB and generate SQL diff versus an original DB');

        $help = <<<HELP
Runs <info>setup:upgrade</info> on the currently configured <info>env.php</info> database,
then generates SQL differences against an existing original database.

This command does <comment>not</comment> clone or import databases.
You must prepare the upgrade target database yourself first.

<comment>Required input:</comment>
  - <info>--original-db</info>: database name before upgrade

<comment>Workflow:</comment>
  1. Ensure <info>env.php</info> points to your upgrade target clone
  2. Run <info>bin/magento setup:upgrade</info> on that target database
  3. Compare <info>--original-db</info> vs upgraded env.php database with <info>mysqldbcompare</info>
  4. Write SQL output to <info>.sql</info> file

<comment>Prerequisites:</comment>
  - <info>mysqldbcompare</info> must be installed and available in PATH
  - The original and upgraded databases must be reachable with env.php credentials

<comment>Important:</comment>
  Always run this on a staging/CI environment first and review generated SQL.

<comment>Example:</comment>
  <info>setup:prepare-upgrade --original-db=production_snapshot -o upgrade.sql</info>
HELP;
        $this->setHelp($help);
    }

    public function isEnabled(): bool
    {
        return function_exists('exec');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->detectDbSettings($output);

        /** @var DatabaseHelper $dbHelper */
        $dbHelper = $this->getDatabaseHelper();
        $dbSettings = $dbHelper->getDbSettings();
        $upgradedDbName = (string) $dbSettings['dbname'];

        $originalDbName = $this->getOriginalDbName($input);
        if ($originalDbName === $upgradedDbName) {
            throw new RuntimeException(
                'Option --original-db must differ from database configured in env.php.'
            );
        }

        $this->validatePrerequisites();
        $this->assertDatabaseExists($dbHelper, $originalDbName, 'Original');

        $this->writeSection($output, 'Prepare Database Upgrade');
        $output->writeln('');
        $output->writeln(sprintf('<comment>Original database:</comment> <info>%s</info>', $originalDbName));
        $output->writeln(sprintf('<comment>Upgrade target database (env.php):</comment> <info>%s</info>', $upgradedDbName));

        // Step 1: Run setup:upgrade on target (env.php) DB
        $output->writeln('');
        $output->writeln('<comment>Running setup:upgrade on target database...</comment>');
        $this->runSetupUpgrade($output);
        $output->writeln('<info>setup:upgrade completed successfully.</info>');

        // Step 2: Diff original vs upgraded using mysqldbcompare
        $output->writeln('');
        $output->writeln('<comment>Comparing original vs upgraded databases with mysqldbcompare...</comment>');

        $skipDataDiff = (bool) $input->getOption('no-data-diff');
        $compareExtraArgs = $this->sanitizeRawExtraArgs((array) $input->getOption('compare-extra-arg'));

        if ($skipDataDiff) {
            $output->writeln('<comment>Skipping data diff (--no-data-diff)</comment>');
        }

        $sqlContent = $this->runMysqlDbCompare(
            $dbHelper,
            $originalDbName,
            $upgradedDbName,
            $skipDataDiff,
            $compareExtraArgs,
            $output
        );

        // Step 3: Write SQL output
        $outputFile = $this->getOutputFile($input);
        if (file_put_contents($outputFile, $sqlContent) === false) {
            throw new RuntimeException(
                sprintf('Unable to write SQL output file "%s".', $outputFile)
            );
        }

        $output->writeln(sprintf('<info>SQL file written to: <comment>%s</comment></info>', $outputFile));

        if ($this->containsSqlStatements($sqlContent)) {
            $output->writeln('<info>mysqldbcompare generated SQL statements.</info>');
        } else {
            $output->writeln('<info>No differences detected by mysqldbcompare.</info>');
        }

        return Command::SUCCESS;
    }

    private function getOriginalDbName(InputInterface $input): string
    {
        $originalDbName = trim((string) $input->getOption('original-db'));
        if ($originalDbName === '') {
            throw new RuntimeException('Option --original-db is required.');
        }

        return $originalDbName;
    }

    private function getOutputFile(InputInterface $input): string
    {
        if ($input->getOption('output-file')) {
            return (string) $input->getOption('output-file');
        }

        return 'upgrade-' . date('Ymd_His') . '.sql';
    }

    private function validatePrerequisites(): void
    {
        if (!$this->commandExists('mysqldbcompare')) {
            throw new RuntimeException(
                'Required binary "mysqldbcompare" is not installed or not in PATH.'
            );
        }
    }

    private function commandExists(string $command): bool
    {
        exec('command -v ' . escapeshellarg($command) . ' >/dev/null 2>&1', $unused, $exitCode);

        return $exitCode === 0;
    }

    /**
     * @param array<int, mixed> $args
     * @return array<int, string>
     */
    private function sanitizeRawExtraArgs(array $args): array
    {
        $sanitized = [];

        foreach ($args as $arg) {
            $arg = trim((string) $arg);
            if ($arg !== '') {
                $sanitized[] = $arg;
            }
        }

        return $sanitized;
    }

    private function runSetupUpgrade(OutputInterface $output): void
    {
        $magentoRootDir = $this->getApplication()->getMagentoRootFolder();

        $phpBinary = OperatingSystem::getPhpBinary();
        $process = new Process(
            [$phpBinary, $magentoRootDir . '/bin/magento', 'setup:upgrade', '--no-interaction'],
            $magentoRootDir
        );
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) use ($output) {
            if ($output->isVerbose()) {
                $output->write($buffer);
            }
        });

        if ($process->getExitCode() !== 0) {
            throw new RuntimeException(
                'setup:upgrade failed with exit code ' . $process->getExitCode()
                . ': ' . $process->getErrorOutput()
            );
        }
    }

    private function runMysqlDbCompare(
        DatabaseHelper $dbHelper,
        string $originalDbName,
        string $upgradedDbName,
        bool $skipDataDiff,
        array $compareExtraArgs,
        OutputInterface $output
    ): string {
        $settings = $dbHelper->getDbSettings();
        $server = $this->buildMysqlDbCompareServerDefinition($settings, $dbHelper->getIsSocketConnect());

        $command = [
            'mysqldbcompare',
            '--server1=' . $server,
            '--server2=' . $server,
            '--difftype=sql',
            '--changes-for=server1',
            '--quiet',
        ];

        if ($skipDataDiff) {
            $command[] = '--skip-data-check';
        }

        foreach ($compareExtraArgs as $arg) {
            $command[] = $arg;
        }

        $command[] = $originalDbName . ':' . $upgradedDbName;

        $process = new Process($command);
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) use ($output) {
            if ($output->isVerbose()) {
                $output->write($buffer);
            }
        });

        if ($process->getExitCode() !== 0) {
            throw new RuntimeException(
                sprintf(
                    'mysqldbcompare failed with exit code %d: %s%s',
                    (int) $process->getExitCode(),
                    $process->getErrorOutput(),
                    $process->getOutput()
                )
            );
        }

        $sqlOutput = trim($process->getOutput());
        if ($sqlOutput === '') {
            $sqlOutput = '-- No differences detected by mysqldbcompare.';
        }

        return $this->buildSqlHeader($originalDbName, $upgradedDbName) . $sqlOutput . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function buildMysqlDbCompareServerDefinition(array $settings, bool $isSocketConnect): string
    {
        $username = (string) $settings['username'];
        $password = (string) $settings['password'];
        $credentials = $password !== '' ? $username . ':' . $password : $username;

        $port = isset($settings['port']) && is_numeric($settings['port'])
            ? (string) ((int) $settings['port'])
            : '3306';

        if ($isSocketConnect) {
            $socket = (string) ($settings['unix_socket'] ?? '');
            if ($socket !== '') {
                return sprintf('%s@localhost:%s:%s', $credentials, $port, $socket);
            }
        }

        $host = (string) ($settings['host'] ?? '127.0.0.1');

        return sprintf('%s@%s:%s', $credentials, $host, $port);
    }

    private function buildSqlHeader(string $originalDbName, string $upgradedDbName): string
    {
        $timestamp = date('Y-m-d H:i:s');

        return
            "-- =====================================================\n"
            . "-- n98-magerun2 setup:prepare-upgrade (mysqldbcompare)\n"
            . "-- Generated: {$timestamp}\n"
            . "-- Original DB: {$originalDbName}\n"
            . "-- Upgraded DB: {$upgradedDbName}\n"
            . "-- =====================================================\n\n";
    }

    private function containsSqlStatements(string $sql): bool
    {
        return (bool) preg_match('/\b(ALTER|CREATE|DROP|RENAME|INSERT|UPDATE|DELETE|TRUNCATE)\b/i', $sql);
    }

    private function assertDatabaseExists(DatabaseHelper $dbHelper, string $dbName, string $label): void
    {
        $db = $dbHelper->getConnection();
        $stmt = $db->query(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ' . $db->quote($dbName)
        );

        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException(sprintf('%s database "%s" does not exist.', $label, $dbName));
        }
    }
}
