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

class DatasetLoaderTest extends TestCase
{
    private function validDatasetPath(): string
    {
        return __DIR__ . '/../_files/bundled-dataset.json';
    }

    public function testLoadsAndReturnsTheBundledDataset(): void
    {
        $loader = new DatasetLoader($this->validDatasetPath());

        $dataset = $loader->load();

        $this->assertSame('bundled-test', $dataset['datasetRevision']);
    }

    public function testThrowsWhenBundledFileIsMissing(): void
    {
        $loader = new DatasetLoader(__DIR__ . '/../_files/does-not-exist.json');

        $this->expectException(DatasetValidationException::class);

        $loader->load();
    }

    public function testThrowsWhenBundledFileIsNotValidJson(): void
    {
        $loader = new DatasetLoader(__DIR__ . '/../_files/invalid-remote.json');

        $this->expectException(DatasetValidationException::class);

        $loader->load();
    }

    public function testThrowsWhenBundledFileFailsSchemaValidation(): void
    {
        $loader = new DatasetLoader(__DIR__ . '/../_files/invalid-dataset.json');

        $this->expectException(DatasetValidationException::class);

        $loader->load();
    }
}
