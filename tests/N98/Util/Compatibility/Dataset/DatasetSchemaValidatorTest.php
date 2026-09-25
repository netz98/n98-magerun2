<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Dataset;

use PHPUnit\Framework\TestCase;

class DatasetSchemaValidatorTest extends TestCase
{
    private DatasetSchemaValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DatasetSchemaValidator();
    }

    private function validDataset(): array
    {
        return [
            'schemaVersion' => '1.0.0',
            'datasetRevision' => '2026.01.1',
            'publishedAt' => '2026-01-01',
            'sources' => [
                ['id' => 'src-1', 'url' => 'https://example.test/docs'],
            ],
            'applications' => [
                [
                    'id' => 'app-1',
                    'product' => 'widgetapp',
                    'edition' => 'any',
                    'context' => 'any',
                    'versionRange' => ['min' => '1.0', 'max' => '1.9'],
                    'dbFamilyCoverage' => ['mysql'],
                    'exhaustive' => true,
                    'sources' => ['src-1'],
                ],
            ],
            'rules' => [
                [
                    'id' => 'rule-1',
                    'product' => 'widgetapp',
                    'editions' => ['community'],
                    'contexts' => ['on-premise'],
                    'appVersionRange' => ['min' => '1.0', 'max' => '1.9'],
                    'dbFamily' => 'mysql',
                    'dbVersionRange' => ['min' => '5.7.0', 'max' => '5.7.99'],
                    'support' => 'supported',
                    'requirement' => 'widgetapp 1.x supports MySQL 5.7.',
                    'sources' => ['src-1'],
                ],
            ],
            'migrations' => [],
        ];
    }

    public function testValidDatasetHasNoErrors(): void
    {
        $this->assertSame([], $this->validator->validate($this->validDataset()));
    }

    public function testMissingTopLevelKeysAreReported(): void
    {
        $errors = $this->validator->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('schemaVersion', implode(' ', $errors));
        $this->assertStringContainsString('rules', implode(' ', $errors));
    }

    public function testRuleWithInvalidSupportValueIsRejected(): void
    {
        $dataset = $this->validDataset();
        $dataset['rules'][0]['support'] = 'maybe';

        $errors = $this->validator->validate($dataset);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('support', implode(' ', $errors));
    }

    public function testRuleReferencingUnknownSourceIsRejected(): void
    {
        $dataset = $this->validDataset();
        $dataset['rules'][0]['sources'] = ['does-not-exist'];

        $errors = $this->validator->validate($dataset);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('unknown source id', implode(' ', $errors));
    }

    public function testDuplicateRuleIdsAreRejected(): void
    {
        $dataset = $this->validDataset();
        $dataset['rules'][] = $dataset['rules'][0];

        $errors = $this->validator->validate($dataset);

        $this->assertContains('rules[].id values must be unique', $errors);
    }

    public function testIncompleteApplicationEntryIsRejected(): void
    {
        $dataset = $this->validDataset();
        unset($dataset['applications'][0]['exhaustive']);

        $errors = $this->validator->validate($dataset);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('exhaustive', implode(' ', $errors));
    }

    public function testAssertValidThrowsOnInvalidDataset(): void
    {
        $this->expectException(DatasetValidationException::class);

        $this->validator->assertValid([]);
    }
}
