<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Dataset;

use JsonException;

/**
 * Loads and schema-validates the bundled db-compatibility dataset (res/db-compatibility.json).
 *
 * The dataset itself is refreshed ahead of time - by scripts/import-db-compatibility-dataset.php
 * during development, or via build.sh's MAGERUN_DB_COMPATIBILITY_DATASET_URL before a release -
 * never at runtime, so a load is always local, fast, and has no network dependency. (Live,
 * per-version freshness is what db:compatibility's --online flag is for, via MagentoWatchClient.)
 */
class DatasetLoader
{
    private string $bundledDatasetPath;

    private DatasetSchemaValidator $validator;

    public function __construct(string $bundledDatasetPath, ?DatasetSchemaValidator $validator = null)
    {
        $this->bundledDatasetPath = $bundledDatasetPath;
        $this->validator = $validator ?? new DatasetSchemaValidator();
    }

    public function load(): array
    {
        if (!is_file($this->bundledDatasetPath)) {
            throw new DatasetValidationException(["Bundled dataset not found at {$this->bundledDatasetPath}"]);
        }

        try {
            $dataset = json_decode((string) file_get_contents($this->bundledDatasetPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DatasetValidationException(['Bundled dataset is not valid JSON: ' . $e->getMessage()]);
        }

        if (!is_array($dataset)) {
            throw new DatasetValidationException(['Bundled dataset is not a valid JSON object']);
        }

        $this->validator->assertValid($dataset);

        return $dataset;
    }
}
