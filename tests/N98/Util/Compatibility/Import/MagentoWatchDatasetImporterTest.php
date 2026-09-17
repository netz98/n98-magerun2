<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Import;

use DateTimeImmutable;
use N98\Util\Compatibility\Dataset\DatasetSchemaValidator;
use PHPUnit\Framework\TestCase;

class MagentoWatchDatasetImporterTest extends TestCase
{
    private MagentoWatchDatasetImporter $importer;

    protected function setUp(): void
    {
        $this->importer = new MagentoWatchDatasetImporter();
    }

    private function baseDataset(): array
    {
        return [
            'schemaVersion' => '1.0.0',
            'datasetRevision' => '2026.09.1',
            'publishedAt' => '2026-09-01',
            'sources' => [
                ['id' => 'hand-authored-source', 'title' => 'Hand-authored', 'url' => 'https://example.test', 'verifiedAt' => '2026-01-01'],
            ],
            'applications' => [
                [
                    'id' => 'hand-authored-app',
                    'product' => 'magento',
                    'edition' => 'any',
                    'context' => 'cloud',
                    'versionRange' => ['min' => '2.4.6', 'max' => '2.4.9-p99'],
                    'dbFamilyCoverage' => [],
                    'exhaustive' => false,
                    'sources' => [],
                ],
            ],
            'rules' => [
                [
                    'id' => 'hand-authored-mysql-legacy-unsupported',
                    'product' => 'magento',
                    'editions' => ['community', 'enterprise'],
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => '2.4.9', 'max' => '2.4.9-p2'],
                    'dbFamily' => 'mysql',
                    'dbVersionRange' => ['min' => '5.0.0', 'max' => '5.7.99'],
                    'support' => 'unsupported',
                    'requirement' => 'MySQL 5.7 and earlier are not supported.',
                    'sources' => ['hand-authored-source'],
                ],
            ],
            'migrations' => [
                ['from' => 'mysql', 'to' => 'mariadb', 'applicableVersions' => ['min' => '2.4.7'], 'guideUrl' => 'https://example.test/migrate'],
            ],
        ];
    }

    private function responses(): array
    {
        return [
            MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMUNITY => [
                '2.4.9' => ['version' => '2.4.9', 'systemRequirements' => ['mysql' => ['8.4'], 'mariadb' => ['11.8', '12.3']]],
                '2.4.8-p4' => ['version' => '2.4.8-p4', 'systemRequirements' => ['mysql' => ['8.4'], 'mariadb' => ['11.4']]],
            ],
            MagentoWatchClient::DISTRIBUTION_MAGENTO_COMMERCE => [
                '2.4.9' => ['version' => '2.4.9', 'systemRequirements' => ['mysql' => ['8.4'], 'mariadb' => ['11.8', '12.3']]],
                // deliberately diverges from community to exercise the edition-split path
                '2.4.8-p4' => ['version' => '2.4.8-p4', 'systemRequirements' => ['mysql' => ['8.4'], 'mariadb' => ['11.4', '10.11']]],
            ],
            MagentoWatchClient::DISTRIBUTION_MAGE_OS => [
                '3.0.0' => ['version' => '3.0.0', 'systemRequirements' => ['mysql' => ['8.4'], 'mariadb' => ['11.4', '12.3']]],
            ],
        ];
    }

    public function testGeneratesMergedRuleWhenCommunityAndEnterpriseMatch(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));

        $rule = $this->findRule($result->dataset(), 'mw-magento-2.4.9-mariadb-11.8');
        $this->assertNotNull($rule);
        $this->assertSame(['community', 'enterprise'], $rule['editions']);
        $this->assertSame(['min' => '2.4.9', 'max' => '2.4.9'], $rule['appVersionRange']);
        $this->assertSame(['min' => '11.8.0', 'max' => '11.8.99'], $rule['dbVersionRange']);
        $this->assertSame('supported', $rule['support']);
        $this->assertCount(2, $rule['sources'], 'should cite both the community and commerce pages when merged');
    }

    public function testSplitsRuleWhenCommunityAndEnterpriseDiverge(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));
        $dataset = $result->dataset();

        $ce = $this->findRule($dataset, 'mw-magento-2.4.8-p4-mariadb-11.4-ce');
        $ee = $this->findRule($dataset, 'mw-magento-2.4.8-p4-mariadb-11.4-ee');
        $eeOnly = $this->findRule($dataset, 'mw-magento-2.4.8-p4-mariadb-10.11-ee');

        $this->assertNotNull($ce);
        $this->assertNotNull($ee);
        $this->assertNotNull($eeOnly, 'commerce-only mariadb 10.11 support should get its own rule');
        $this->assertSame(['community'], $ce['editions']);
        $this->assertSame(['enterprise'], $ee['editions']);
        $this->assertNull($this->findRule($dataset, 'mw-magento-2.4.8-p4-mariadb-11.4'), 'no merged rule should exist when they diverge');
    }

    public function testMageOsGetsItsOwnProductScope(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));
        $dataset = $result->dataset();

        $rule = $this->findRule($dataset, 'mw-mage-os-3.0.0-mariadb-11.4');
        $this->assertNotNull($rule);
        $this->assertSame('mage-os', $rule['product']);
        $this->assertSame(['community'], $rule['editions']);

        $application = null;
        foreach ($dataset['applications'] as $app) {
            if ($app['id'] === 'mw-mage-os-community') {
                $application = $app;
            }
        }
        $this->assertNotNull($application);
        $this->assertSame('mage-os', $application['product']);
    }

    public function testHandAuthoredEntriesAreNeverTouched(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));
        $dataset = $result->dataset();

        $this->assertNotNull($this->findRule($dataset, 'hand-authored-mysql-legacy-unsupported'));
        $this->assertSame($this->baseDataset()['rules'][0], $this->findRule($dataset, 'hand-authored-mysql-legacy-unsupported'));

        $handAuthoredApp = null;
        foreach ($dataset['applications'] as $app) {
            if ($app['id'] === 'hand-authored-app') {
                $handAuthoredApp = $app;
            }
        }
        $this->assertSame($this->baseDataset()['applications'][0], $handAuthoredApp);

        $this->assertSame($this->baseDataset()['migrations'], $dataset['migrations']);
    }

    public function testImportedDatasetPassesSchemaValidation(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));

        $errors = (new DatasetSchemaValidator())->validate($result->dataset());

        $this->assertSame([], $errors);
    }

    public function testRevisionAndPublishedAtBumpOnlyWhenThereAreChanges(): void
    {
        $result = $this->importer->import($this->baseDataset(), $this->responses(), new DateTimeImmutable('2026-09-20'));

        $this->assertSame('2026.09.2', $result->dataset()['datasetRevision']);
        $this->assertSame('2026-09-20', $result->dataset()['publishedAt']);
    }

    public function testRunningTwiceWithSameInputIsIdempotent(): void
    {
        $now = new DateTimeImmutable('2026-09-20');
        $first = $this->importer->import($this->baseDataset(), $this->responses(), $now);
        $second = $this->importer->import($first->dataset(), $this->responses(), $now);

        $this->assertFalse($second->hasChanges());
        $this->assertCount(count($first->dataset()['rules']), $second->dataset()['rules']);
        $this->assertSame($first->dataset(), $second->dataset());
    }

    public function testRemovesOwnedRuleNoLongerReportedUpstream(): void
    {
        $now = new DateTimeImmutable('2026-09-20');
        $first = $this->importer->import($this->baseDataset(), $this->responses(), $now);

        $shrunkResponses = $this->responses();
        unset($shrunkResponses[MagentoWatchClient::DISTRIBUTION_MAGE_OS]['3.0.0']);

        $second = $this->importer->import($first->dataset(), $shrunkResponses, $now);

        $this->assertContains('mw-mage-os-3.0.0-mariadb-11.4', $second->removedRuleIds());
        $this->assertNull($this->findRule($second->dataset(), 'mw-mage-os-3.0.0-mariadb-11.4'));
        // hand-authored content must still be there
        $this->assertNotNull($this->findRule($second->dataset(), 'hand-authored-mysql-legacy-unsupported'));
    }

    private function findRule(array $dataset, string $id): ?array
    {
        foreach ($dataset['rules'] as $rule) {
            if ($rule['id'] === $id) {
                return $rule;
            }
        }

        return null;
    }
}
