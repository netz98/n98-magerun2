<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

use PHPUnit\Framework\TestCase;

class RuleEvaluatorTest extends TestCase
{
    private RuleEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new RuleEvaluator();
    }

    private function dataset(): array
    {
        return [
            'schemaVersion' => '1.0.0',
            'datasetRevision' => 'test',
            'publishedAt' => '2026-01-01',
            'sources' => [
                ['id' => 'src-vendor-docs', 'title' => 'Vendor docs', 'url' => 'https://example.test/docs', 'verifiedAt' => '2026-01-01'],
            ],
            'applications' => [
                [
                    'id' => 'app-1x',
                    'product' => 'widgetapp',
                    'edition' => 'any',
                    'context' => 'any',
                    'versionRange' => ['min' => '1.0', 'max' => '1.9'],
                    'dbFamilyCoverage' => ['mysql'],
                    'exhaustive' => true,
                    'sources' => ['src-vendor-docs'],
                ],
                [
                    'id' => 'app-2x',
                    'product' => 'widgetapp',
                    'edition' => 'any',
                    'context' => 'any',
                    'versionRange' => ['min' => '2.0', 'max' => '2.9'],
                    'dbFamilyCoverage' => ['mysql', 'mariadb'],
                    'exhaustive' => true,
                    'sources' => ['src-vendor-docs'],
                ],
                [
                    'id' => 'app-2x-cloud',
                    'product' => 'widgetapp',
                    'edition' => 'enterprise',
                    'context' => 'cloud',
                    'versionRange' => ['min' => '2.0', 'max' => '2.9'],
                    'dbFamilyCoverage' => [],
                    'exhaustive' => false,
                    'sources' => ['src-vendor-docs'],
                ],
            ],
            'rules' => [
                [
                    'id' => 'widgetapp-1x-mysql-5.7',
                    'product' => 'widgetapp',
                    'editions' => ['community', 'enterprise'],
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => '1.0', 'max' => '1.9'],
                    'dbFamily' => 'mysql',
                    'dbVersionRange' => ['min' => '5.7.0', 'max' => '5.7.99'],
                    'support' => 'supported',
                    'requirement' => 'widgetapp 1.x supports MySQL 5.7.',
                    'recommended' => ['version' => '5.7.44', 'rationale' => 'latest 5.7 patch', 'recommendedBy' => 'Vendor'],
                    'sources' => ['src-vendor-docs'],
                ],
                [
                    'id' => 'widgetapp-2x-mariadb-10.5',
                    'product' => 'widgetapp',
                    'editions' => ['community'],
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => '2.0', 'max' => '2.6-p5'],
                    'dbFamily' => 'mariadb',
                    'dbVersionRange' => ['min' => '10.5.0', 'max' => '10.5.99'],
                    'support' => 'supported',
                    'requirement' => 'widgetapp 2.0-2.6-p5 community supports MariaDB 10.5.',
                    'sources' => ['src-vendor-docs'],
                ],
                [
                    'id' => 'widgetapp-2x-mariadb-legacy-unsupported',
                    'product' => 'widgetapp',
                    'editions' => ['community'],
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => '2.0', 'max' => '2.9'],
                    'dbFamily' => 'mariadb',
                    'dbVersionRange' => ['min' => '10.0.0', 'max' => '10.1.99'],
                    'support' => 'unsupported',
                    'requirement' => 'MariaDB below 10.5 is not supported by widgetapp 2.x.',
                    'sources' => ['src-vendor-docs'],
                ],
            ],
            'migrations' => [
                [
                    'from' => 'mysql',
                    'to' => 'mariadb',
                    'applicableVersions' => ['min' => '2.0'],
                    'guideUrl' => 'https://example.test/migrate-mysql-mariadb',
                    'notes' => 'Test migration guidance.',
                ],
            ],
        ];
    }

    private function context(
        string $edition = 'community',
        string $context = 'on-premise',
        string $appVersion = '1.0',
        string $dbFamily = 'mysql',
        string $dbVersion = '5.7.44'
    ): EnvironmentContext {
        return new EnvironmentContext('widgetapp', $edition, $context, true, $appVersion, $dbFamily, $dbVersion);
    }

    public function testSupportedMatchOnExactPatchVersion(): void
    {
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            appVersion: '2.6-p3',
            dbFamily: 'mariadb',
            dbVersion: '10.5.9-MariaDB'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_SUPPORTED, $assessment->status());
        $this->assertSame('widgetapp-2x-mariadb-10.5', $assessment->matchedRuleId());
    }

    public function testPatchVersionJustOutsideRuleRangeDoesNotMatch(): void
    {
        // rule only covers up to 2.6-p5; 2.6-p6 falls back to the app-scope (family covered, no rule) -> unknown
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            appVersion: '2.6-p6',
            dbFamily: 'mariadb',
            dbVersion: '10.5.9-MariaDB'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_UNKNOWN, $assessment->status());
    }

    public function testExplicitUnsupportedRuleWins(): void
    {
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            appVersion: '2.5',
            dbFamily: 'mariadb',
            dbVersion: '10.1.40-MariaDB'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_UNSUPPORTED, $assessment->status());
        $this->assertSame('widgetapp-2x-mariadb-legacy-unsupported', $assessment->matchedRuleId());
    }

    public function testFamilyNotCoveredAndExhaustiveIsUnsupported(): void
    {
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            appVersion: '1.5',
            dbFamily: 'mariadb',
            dbVersion: '10.5.9-MariaDB'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_UNSUPPORTED, $assessment->status());
        $this->assertNull($assessment->matchedRuleId());
    }

    public function testUnmatchedVersionWithinCoveredFamilyIsUnknownNotUnsupported(): void
    {
        // mysql is covered for 2.x, but no rule addresses mysql 9.9 specifically
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            appVersion: '2.1',
            dbFamily: 'mysql',
            dbVersion: '9.9.0'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_UNKNOWN, $assessment->status());
    }

    public function testNoApplicationScopeIsUnknown(): void
    {
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(appVersion: '9.0'));

        $this->assertSame(CompatibilityAssessment::STATUS_UNKNOWN, $assessment->status());
        $this->assertNull($assessment->matchedRuleId());
    }

    public function testEditionAndContextDifferentiateVerdicts(): void
    {
        // community/on-premise: matches the explicit rule -> supported
        $onPremise = $this->evaluator->evaluate($this->dataset(), $this->context(
            edition: 'community',
            context: 'on-premise',
            appVersion: '1.5',
            dbFamily: 'mysql',
            dbVersion: '5.7.40'
        ));
        $this->assertSame(CompatibilityAssessment::STATUS_SUPPORTED, $onPremise->status());

        // same product/version/db, but cloud context has no rules referencing it -> falls back to
        // the family-covered/no-rule-matched path -> unknown, never a false "supported"
        $cloud = $this->evaluator->evaluate($this->dataset(), $this->context(
            edition: 'community',
            context: 'cloud',
            appVersion: '1.5',
            dbFamily: 'mysql',
            dbVersion: '5.7.40'
        ));
        $this->assertSame(CompatibilityAssessment::STATUS_UNKNOWN, $cloud->status());
    }

    public function testEnterpriseCloudScopeIsAlwaysUnknown(): void
    {
        $assessment = $this->evaluator->evaluate($this->dataset(), $this->context(
            edition: 'enterprise',
            context: 'cloud',
            appVersion: '2.1',
            dbFamily: 'mysql',
            dbVersion: '8.0.40'
        ));

        $this->assertSame(CompatibilityAssessment::STATUS_UNKNOWN, $assessment->status());
    }

    public function testMigrationGuidanceIsFoundForApplicableVersion(): void
    {
        $guidance = $this->evaluator->findMigrationGuidance($this->dataset(), 'mysql', 'mariadb', '2.5');

        $this->assertNotNull($guidance);
        $this->assertSame('https://example.test/migrate-mysql-mariadb', $guidance['guideUrl']);
    }

    public function testMigrationGuidanceIsNullOutsideApplicableVersions(): void
    {
        $guidance = $this->evaluator->findMigrationGuidance($this->dataset(), 'mysql', 'mariadb', '1.5');

        $this->assertNull($guidance);
    }
}
