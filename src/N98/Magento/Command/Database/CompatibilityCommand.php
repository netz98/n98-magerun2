<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Database;

use Magento\Framework\App\DistributionMetadataInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Compatibility\CompatibilityAssessment;
use N98\Util\Compatibility\Dataset\DatasetLoader;
use N98\Util\Compatibility\EnvironmentContext;
use N98\Util\Compatibility\Import\MagentoWatchClient;
use N98\Util\Compatibility\Import\MagentoWatchDatasetImporter;
use N98\Util\Compatibility\MariaDbLifecycleClient;
use N98\Util\Compatibility\RuleEvaluator;
use N98\Util\Console\MagerunStyle;
use N98\Util\ProjectComposer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Read-only db:compatibility command.
 *
 * Evaluates the installed (and, optionally, a proposed target) Magento/Adobe Commerce +
 * MySQL/MariaDB combination against the versioned dataset at ./res/db-compatibility.json.
 *
 * A "supported" result only confirms dataset-based version compatibility. It does not certify
 * schema, data, extension, or migration compatibility.
 */
class CompatibilityCommand extends AbstractMagentoCommand
{
    private ProductMetadataInterface $productMetadata;

    private ResourceConnection $resourceConnection;

    public function inject(ProductMetadataInterface $productMetadata, ResourceConnection $resourceConnection): void
    {
        $this->productMetadata = $productMetadata;
        $this->resourceConnection = $resourceConnection;
    }

    protected function configure(): void
    {
        $this
            ->setName('db:compatibility')
            ->setDescription(
                'Assesses whether the installed (or a proposed target) application/database combination '
                . 'is officially supported'
            )
            ->addOption(
                'target-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Application version to evaluate instead of the detected one, e.g. 2.4.9'
            )
            ->addOption(
                'target-db',
                null,
                InputOption::VALUE_REQUIRED,
                'Database family to evaluate instead of the detected one: mysql or mariadb'
            )
            ->addOption(
                'target-db-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Database version to evaluate instead of the detected one, e.g. 10.11 or 11.4'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format: text or json',
                'text'
            )
            ->addOption(
                'timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Bounded timeout in seconds for the optional --online check and MariaDB lifecycle lookup',
                '5'
            )
            ->addOption(
                'online',
                null,
                InputOption::VALUE_NONE,
                'Additionally look up the current/target application version live at magento.watch, '
                . 'and prefer that over the bundled dataset when it succeeds'
            );

        $this->setHelp(
            <<<HELP
The <info>db:compatibility</info> command evaluates whether the installed (or a proposed target)
application/database combination is officially supported, using a versioned dataset shipped at
<comment>./res/db-compatibility.json</comment>. Most of that dataset is generated from
<comment>magento.watch</comment> (https://magento.watch/api), an independent community API tracking
exact per-release MySQL/MariaDB requirements; pass <info>--online</info> to check it live instead of
relying solely on the bundled snapshot.

  <info>n98-magerun2 db:compatibility</info>
  <info>n98-magerun2 db:compatibility --target-version=2.4.9</info>
  <info>n98-magerun2 db:compatibility --target-version=2.4.9 --target-db=mariadb --target-db-version=11.8</info>
  <info>n98-magerun2 db:compatibility --online</info>
  <info>n98-magerun2 db:compatibility --format=json</info>

Standard output keeps to the essentials (the verdict and why); pass <info>-v</info> for the full
evaluated-configuration comparison, dataset metadata, finding IDs, source citations, and database
lifecycle details. <info>--format=json</info> always includes everything regardless of -v.

A "supported" result confirms dataset-based version compatibility only. It does not certify
schema, data, extension, or migration compatibility. Automatic migration and database
modifications are outside the scope of this command.
HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeout = max(1, (int) $input->getOption('timeout'));
        $online = (bool) $input->getOption('online');
        $asJson = $input->getOption('format') === 'json';
        $hasTargetOverride = $input->getOption('target-version') !== null
            || $input->getOption('target-db') !== null
            || $input->getOption('target-db-version') !== null;

        $current = $this->detectCurrentContext();
        $target = $current->withOverrides(
            $input->getOption('target-version'),
            $input->getOption('target-db'),
            $input->getOption('target-db-version')
        );

        $dataset = (new DatasetLoader($this->bundledDatasetPath()))->load();
        $evaluator = new RuleEvaluator();

        if ($online) {
            $currentEntry = $this->fetchLiveEntry($current, $timeout);
            $targetEntry = $this->sameLiveLookup($current, $target) ? $currentEntry : $this->fetchLiveEntry($target, $timeout);

            [$currentAssessment, $currentDataSource, $currentDataSourceNote]
                = $this->assessWithOptionalLiveEntry($current, $currentEntry, $dataset, $evaluator);
            [$targetAssessment, $targetDataSource, $targetDataSourceNote]
                = $this->assessWithOptionalLiveEntry($target, $targetEntry, $dataset, $evaluator);
        } else {
            $currentAssessment = $evaluator->evaluate($dataset, $current);
            $targetAssessment = $evaluator->evaluate($dataset, $target);
            $currentDataSource = $targetDataSource = 'bundled';
            $currentDataSourceNote = $targetDataSourceNote = null;
        }

        $migration = null;
        if ($current->dbFamily() !== $target->dbFamily()) {
            $migration = $evaluator->findMigrationGuidance(
                $dataset,
                $current->dbFamily(),
                $target->dbFamily(),
                $target->appVersion()
            );
        }

        // Lifecycle is display-only enrichment, only ever shown in verbose text output - skip the
        // network round trip entirely in the common (non-verbose, non-json) case. JSON output stays
        // complete regardless of -v, since it's a stable machine-readable contract, not a UX concern.
        $currentLifecycle = $targetLifecycle = null;
        if ($output->isVerbose() || $asJson) {
            $lifecycleClient = new MariaDbLifecycleClient();
            $currentLifecycle = $this->lookupLifecycle($lifecycleClient, $current, $timeout);
            $targetLifecycle = $current->dbVersion() === $target->dbVersion() && $current->dbFamily() === $target->dbFamily()
                ? $currentLifecycle
                : $this->lookupLifecycle($lifecycleClient, $target, $timeout);
        }

        if ($asJson) {
            $this->renderJson(
                $output,
                $dataset,
                $current,
                $currentAssessment,
                $currentLifecycle,
                $currentDataSource,
                $currentDataSourceNote,
                $target,
                $targetAssessment,
                $targetLifecycle,
                $targetDataSource,
                $targetDataSourceNote,
                $migration
            );
        } else {
            $this->renderText(
                $output,
                $dataset,
                $hasTargetOverride,
                $current,
                $currentAssessment,
                $currentLifecycle,
                $currentDataSourceNote,
                $target,
                $targetAssessment,
                $targetLifecycle,
                $targetDataSourceNote,
                $migration
            );
        }

        return $currentAssessment->isUnsupported() ? Command::FAILURE : Command::SUCCESS;
    }

    private function detectCurrentContext(): EnvironmentContext
    {
        $composer = new ProjectComposer($this->_magentoRootFolder);
        [$product, $edition, $appVersion] = $this->detectProductEditionAndVersion($composer);
        [$deploymentContext, $contextIsCertain] = $this->detectDeploymentContext($composer);
        [$dbFamily, $dbVersion] = $this->detectDatabase();

        return new EnvironmentContext(
            $product,
            $edition,
            $deploymentContext,
            $contextIsCertain,
            $appVersion,
            $dbFamily,
            $dbVersion
        );
    }

    /**
     * Mage-OS is a distinct distribution with its own version numbering (e.g. "3.0.0"), not the
     * "2.4.x" numbering Magento Open Source/Adobe Commerce share - it needs its own product scope
     * in the dataset rather than being silently evaluated as plain "magento" via its underlying
     * core-compatible version.
     *
     * @return array{0: string, 1: string, 2: string} product, edition, appVersion
     */
    private function detectProductEditionAndVersion(ProjectComposer $composer): array
    {
        if ($this->isMageOs()) {
            $version = $this->productMetadata instanceof DistributionMetadataInterface
                ? (string) $this->productMetadata->getDistributionVersion()
                : '';

            if ($version === '') {
                $version = $this->detectComposerPackageVersion($composer, 'mage-os/product-community-edition')
                    ?? (string) $this->productMetadata->getVersion();
            }

            return ['mage-os', 'community', $version];
        }

        $edition = stripos($this->productMetadata->getEdition(), 'enterprise') !== false ? 'enterprise' : 'community';
        $packageName = $edition === 'enterprise'
            ? 'magento/product-enterprise-edition'
            : 'magento/product-community-edition';
        $version = $this->detectComposerPackageVersion($composer, $packageName)
            ?? (string) $this->productMetadata->getVersion();

        return ['magento', $edition, $version];
    }

    private function isMageOs(): bool
    {
        return $this->productMetadata instanceof DistributionMetadataInterface
            && stripos((string) $this->productMetadata->getDistributionName(), 'mage-os') !== false;
    }

    private function detectComposerPackageVersion(ProjectComposer $composer, string $packageName): ?string
    {
        if (!$composer->isLockFile()) {
            return null;
        }

        $packages = $composer->getComposerLockPackages();
        if (!isset($packages[$packageName]['version'])) {
            return null;
        }

        return ltrim((string) $packages[$packageName]['version'], 'v');
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function detectDeploymentContext(ProjectComposer $composer): array
    {
        if (!$composer->isLockFile()) {
            return [EnvironmentContext::CONTEXT_UNKNOWN, false];
        }

        $packages = $composer->getComposerLockPackages();

        if (isset($packages['magento/ece-tools']) || isset($packages['magento/magento-cloud-components'])) {
            return ['cloud', true];
        }

        return ['on-premise', true];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function detectDatabase(): array
    {
        $rawVersion = (string) $this->resourceConnection->getConnection()->fetchOne('SELECT VERSION()');
        $family = stripos($rawVersion, 'mariadb') !== false ? 'mariadb' : 'mysql';

        return [$family, $rawVersion];
    }

    /**
     * Live magento.watch lookup for one environment's exact application version, for --online.
     * Returns null on any failure (no matching distribution, unreachable, unknown version) - the
     * caller falls back to the bundled dataset.
     */
    private function fetchLiveEntry(EnvironmentContext $context, int $timeout): ?array
    {
        $distribution = $this->distributionSlug($context->product(), $context->edition());
        if ($distribution === null) {
            return null;
        }

        return (new MagentoWatchClient())->fetchVersion($distribution, $context->appVersion(), $timeout);
    }

    /**
     * True when current and target refer to the exact same magento.watch lookup (same product,
     * edition, and application version), so a second live HTTP request would be redundant - the
     * database family/version can still differ and get evaluated separately against the same entry.
     */
    private function sameLiveLookup(EnvironmentContext $current, EnvironmentContext $target): bool
    {
        return $current->product() === $target->product()
            && $current->edition() === $target->edition()
            && $current->appVersion() === $target->appVersion();
    }

    private function distributionSlug(string $product, string $edition): ?string
    {
        if ($product === 'mage-os') {
            return MagentoWatchClient::DISTRIBUTION_MAGE_OS;
        }

        if ($product === 'magento') {
            return $edition === 'enterprise'
                ? MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMERCE
                : MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMUNITY;
        }

        return null;
    }

    /**
     * @return array{0: CompatibilityAssessment, 1: string, 2: string} assessment, data source
     *         ("bundled"|"magento-watch-live"), and a human-readable note explaining which and why
     */
    private function assessWithOptionalLiveEntry(
        EnvironmentContext $context,
        ?array $liveEntry,
        array $bundledDataset,
        RuleEvaluator $evaluator
    ): array {
        if ($liveEntry === null) {
            return [
                $evaluator->evaluate($bundledDataset, $context),
                'bundled',
                'magento.watch live check unavailable for this version; used the bundled dataset instead.',
            ];
        }

        $distribution = $this->distributionSlug($context->product(), $context->edition());
        $syntheticDataset = (new MagentoWatchDatasetImporter())->import(
            $this->emptyDatasetSkeleton(),
            [$distribution => [$context->appVersion() => $liveEntry]]
        )->dataset();

        return [
            $evaluator->evaluate($syntheticDataset, $context),
            'magento-watch-live',
            'Checked live against magento.watch just now.',
        ];
    }

    private function emptyDatasetSkeleton(): array
    {
        return [
            'schemaVersion' => '1.0.0',
            'datasetRevision' => 'live',
            'publishedAt' => date('Y-m-d'),
            'sources' => [],
            'applications' => [],
            'rules' => [],
            'migrations' => [],
        ];
    }

    private function lookupLifecycle(MariaDbLifecycleClient $client, EnvironmentContext $context, int $timeout): ?array
    {
        if ($context->dbFamily() !== 'mariadb') {
            return null;
        }

        $lifecycle = $client->lookup($context->dbVersion(), $timeout);

        return $lifecycle?->toArray();
    }

    private function bundledDatasetPath(): string
    {
        return dirname(__DIR__, 5) . '/res/db-compatibility.json';
    }

    private function renderText(
        OutputInterface $output,
        array $dataset,
        bool $hasTargetOverride,
        EnvironmentContext $current,
        CompatibilityAssessment $currentAssessment,
        ?array $currentLifecycle,
        ?string $currentDataSourceNote,
        EnvironmentContext $target,
        CompatibilityAssessment $targetAssessment,
        ?array $targetLifecycle,
        ?string $targetDataSourceNote,
        ?array $migration
    ): void {
        $io = $this->io;
        $verbose = $output->isVerbose();

        $io->heading('Database compatibility');

        if ($hasTargetOverride || $verbose) {
            $headers = ['', 'Current (detected)'];
            $rows = [
                ['Product', $current->product()],
                ['Edition', $current->edition()],
                ['Deployment context', $this->formatContext($current)],
                ['Application version', $current->appVersion()],
                ['Database family', $current->dbFamily()],
                ['Database version', $current->dbVersion()],
            ];

            if ($hasTargetOverride) {
                $headers[] = 'Target';
                $rows[0][] = $target->product();
                $rows[1][] = $target->edition();
                $rows[2][] = $this->formatContext($target);
                $rows[3][] = $target->appVersion();
                $rows[4][] = $target->dbFamily();
                $rows[5][] = $target->dbVersion();
            }

            $io->subheading('Evaluated configuration');
            $io->table($headers, $rows);
        } else {
            // Nothing to compare against and not asked for detail - a table would just repeat
            // itself across two identical columns.
            $io->item(sprintf(
                '%s %s (%s, %s) on %s %s',
                $current->product(),
                $current->appVersion(),
                $current->edition(),
                $this->formatContext($current),
                $current->dbFamily(),
                $current->dbVersion()
            ));
        }

        if ($verbose) {
            $io->subheading('Dataset');
            $io->keyValue([
                'Revision' => (string) ($dataset['datasetRevision'] ?? 'unknown'),
                'Published' => (string) ($dataset['publishedAt'] ?? 'unknown'),
            ]);
        }

        $io->subheading('Support status');
        $this->renderAssessmentText($io, $hasTargetOverride ? 'Current' : null, $currentAssessment, $currentDataSourceNote, $verbose);

        if ($hasTargetOverride) {
            $io->newLine();
            $this->renderAssessmentText($io, 'Target', $targetAssessment, $targetDataSourceNote, $verbose);
        }

        if ($verbose) {
            $io->subheading('Database lifecycle');
            $this->renderLifecycleTable(
                $io,
                $current,
                $currentLifecycle,
                $hasTargetOverride ? $target : null,
                $hasTargetOverride ? $targetLifecycle : null
            );
        }

        if ($migration !== null) {
            $io->subheading('Migration guidance');
            $io->item(sprintf('%s -> %s: %s', $migration['from'], $migration['to'], $migration['guideUrl']));
            if (!empty($migration['notes'])) {
                $io->detail($migration['notes']);
            }
        }

        if ($verbose) {
            $io->newLine();
            $io->hint(
                'A "supported" result confirms dataset-based version compatibility only. It does not certify '
                . 'schema, data, extension, or migration compatibility.'
            );
        }
    }

    /**
     * @param string|null $label omit (null) when there is no target to distinguish it from - the
     *        bare status line reads better as "SUPPORTED" than "Current: SUPPORTED" when it's the
     *        only result being shown
     */
    private function renderAssessmentText(
        MagerunStyle $io,
        ?string $label,
        CompatibilityAssessment $assessment,
        ?string $dataSourceNote,
        bool $verbose
    ): void {
        $line = $label !== null
            ? sprintf('%s: %s', $label, strtoupper($assessment->status()))
            : strtoupper($assessment->status());

        switch ($assessment->status()) {
            case CompatibilityAssessment::STATUS_SUPPORTED:
                $io->ok($line);
                break;
            case CompatibilityAssessment::STATUS_UNSUPPORTED:
                $io->fail($line);
                break;
            default:
                $io->warn($line);
                break;
        }

        $io->item($assessment->requirement());

        if ($dataSourceNote !== null) {
            $io->item($dataSourceNote);
        }

        foreach ($assessment->recommendations() as $recommendation) {
            $io->item(sprintf(
                'Recommended: %s%s%s',
                $recommendation['version'] ?? 'n/a',
                $recommendation['rationale'] !== null ? ' — ' . $recommendation['rationale'] : '',
                $recommendation['recommendedBy'] !== null ? ' (' . $recommendation['recommendedBy'] . ')' : ''
            ));
        }

        if (!$verbose) {
            return;
        }

        $io->hint('Finding ID: ' . $assessment->findingId());
        foreach ($assessment->evidence() as $source) {
            $io->hint(sprintf(
                'Source: %s%s%s',
                $source['title'] ?? $source['id'],
                $source['url'] !== null ? ' — ' . $source['url'] : '',
                $source['verifiedAt'] !== null ? ' (verified ' . $source['verifiedAt'] . ')' : ''
            ));
        }
    }

    private function renderLifecycleTable(
        MagerunStyle $io,
        EnvironmentContext $current,
        ?array $currentLifecycle,
        ?EnvironmentContext $target,
        ?array $targetLifecycle
    ): void {
        $currentColumn = $this->lifecycleColumn($current, $currentLifecycle);

        $headers = ['', 'Current'];
        $rows = [
            ['Status', $currentColumn['status']],
            ['Support type', $currentColumn['supportType']],
            ['EOL date', $currentColumn['eolDate']],
        ];

        if ($target !== null) {
            $targetColumn = $this->lifecycleColumn($target, $targetLifecycle);
            $headers[] = 'Target';
            $rows[0][] = $targetColumn['status'];
            $rows[1][] = $targetColumn['supportType'];
            $rows[2][] = $targetColumn['eolDate'];
        }

        $io->table($headers, $rows);
    }

    /**
     * @return array{status: string, supportType: string, eolDate: string}
     */
    private function lifecycleColumn(EnvironmentContext $context, ?array $lifecycle): array
    {
        if ($context->dbFamily() !== 'mariadb') {
            $value = 'n/a (no automated source for ' . $context->dbFamily() . ')';

            return ['status' => $value, 'supportType' => $value, 'eolDate' => $value];
        }

        if ($lifecycle === null) {
            $value = 'unavailable (offline or unreachable)';

            return ['status' => $value, 'supportType' => $value, 'eolDate' => $value];
        }

        return [
            'status' => (string) ($lifecycle['status'] ?? 'unknown'),
            'supportType' => (string) ($lifecycle['supportType'] ?? 'unknown'),
            'eolDate' => (string) ($lifecycle['eolDate'] ?? 'n/a'),
        ];
    }

    private function formatContext(EnvironmentContext $context): string
    {
        return $context->deploymentContextIsCertain()
            ? $context->deploymentContext()
            : $context->deploymentContext() . ' (uncertain)';
    }

    private function renderJson(
        OutputInterface $output,
        array $dataset,
        EnvironmentContext $current,
        CompatibilityAssessment $currentAssessment,
        ?array $currentLifecycle,
        string $currentDataSource,
        ?string $currentDataSourceNote,
        EnvironmentContext $target,
        CompatibilityAssessment $targetAssessment,
        ?array $targetLifecycle,
        string $targetDataSource,
        ?string $targetDataSourceNote,
        ?array $migration
    ): void {
        $payload = [
            'meta' => [
                'datasetSchemaVersion' => $dataset['schemaVersion'] ?? null,
                'datasetRevision' => $dataset['datasetRevision'] ?? null,
                'datasetPublishedAt' => $dataset['publishedAt'] ?? null,
            ],
            'current' => [
                'environment' => $current->toArray(),
                'assessment' => $currentAssessment->toArray(),
                'lifecycle' => $currentLifecycle,
                'dataSource' => $currentDataSource,
                'dataSourceNote' => $currentDataSourceNote,
            ],
            'target' => [
                'environment' => $target->toArray(),
                'assessment' => $targetAssessment->toArray(),
                'lifecycle' => $targetLifecycle,
                'dataSource' => $targetDataSource,
                'dataSourceNote' => $targetDataSourceNote,
            ],
            'migration' => $migration,
        ];

        $output->writeln((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
