<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility\Dataset;

/**
 * Structural validator for the db-compatibility dataset, documented as JSON Schema at
 * res/db-compatibility.schema.json. This checks the fixed shape our own code relies on
 * (required keys, types, enum values) rather than implementing a general-purpose JSON
 * Schema draft engine - the project has no such dependency today and this dataset's
 * structure is fully under our control.
 *
 * @return string[] validation errors, empty when the dataset is valid
 */
class DatasetSchemaValidator
{
    public function validate(array $dataset): array
    {
        $errors = [];

        $this->requireString($dataset, 'schemaVersion', $errors);
        $this->requireString($dataset, 'datasetRevision', $errors);
        $this->requireString($dataset, 'publishedAt', $errors);
        $this->requireArray($dataset, 'sources', $errors);
        $this->requireArray($dataset, 'applications', $errors);
        $this->requireArray($dataset, 'rules', $errors);
        $this->requireArray($dataset, 'migrations', $errors);

        if (!empty($errors)) {
            return $errors;
        }

        foreach ($dataset['sources'] as $index => $source) {
            $this->validateSource($source, $index, $errors);
        }

        $sourceIds = array_column($dataset['sources'], 'id');

        foreach ($dataset['applications'] as $index => $application) {
            $this->validateApplication($application, $index, $sourceIds, $errors);
        }

        $applicationIds = array_column($dataset['applications'], 'id');

        foreach ($dataset['rules'] as $index => $rule) {
            $this->validateRule($rule, $index, $sourceIds, $errors);
        }

        foreach ($dataset['migrations'] as $index => $migration) {
            $this->validateMigration($migration, $index, $errors);
        }

        if (count(array_unique($applicationIds)) !== count($applicationIds)) {
            $errors[] = 'applications[].id values must be unique';
        }

        $ruleIds = array_column($dataset['rules'], 'id');
        if (count(array_unique($ruleIds)) !== count($ruleIds)) {
            $errors[] = 'rules[].id values must be unique';
        }

        return $errors;
    }

    public function assertValid(array $dataset): void
    {
        $errors = $this->validate($dataset);
        if (!empty($errors)) {
            throw new DatasetValidationException($errors);
        }
    }

    private function validateSource(mixed $source, int $index, array &$errors): void
    {
        if (!is_array($source)) {
            $errors[] = "sources[$index] must be an object";
            return;
        }

        $this->requireString($source, 'id', $errors, "sources[$index].");
        $this->requireString($source, 'url', $errors, "sources[$index].");
    }

    private function validateApplication(mixed $application, int $index, array $sourceIds, array &$errors): void
    {
        if (!is_array($application)) {
            $errors[] = "applications[$index] must be an object";
            return;
        }

        $prefix = "applications[$index].";
        $this->requireString($application, 'id', $errors, $prefix);
        $this->requireString($application, 'product', $errors, $prefix);
        $this->requireString($application, 'edition', $errors, $prefix);
        $this->requireString($application, 'context', $errors, $prefix);
        $this->requireArray($application, 'versionRange', $errors, $prefix);
        $this->requireArray($application, 'dbFamilyCoverage', $errors, $prefix);

        if (!array_key_exists('exhaustive', $application) || !is_bool($application['exhaustive'])) {
            $errors[] = $prefix . 'exhaustive must be a boolean';
        }

        $this->requireKnownSources($application['sources'] ?? [], $sourceIds, $prefix, $errors);
    }

    private function validateRule(mixed $rule, int $index, array $sourceIds, array &$errors): void
    {
        if (!is_array($rule)) {
            $errors[] = "rules[$index] must be an object";
            return;
        }

        $prefix = "rules[$index].";
        $this->requireString($rule, 'id', $errors, $prefix);
        $this->requireString($rule, 'product', $errors, $prefix);
        $this->requireArray($rule, 'editions', $errors, $prefix);
        $this->requireArray($rule, 'contexts', $errors, $prefix);
        $this->requireArray($rule, 'appVersionRange', $errors, $prefix);
        $this->requireString($rule, 'dbFamily', $errors, $prefix);
        $this->requireArray($rule, 'dbVersionRange', $errors, $prefix);
        $this->requireString($rule, 'requirement', $errors, $prefix);

        $support = $rule['support'] ?? null;
        if (!in_array($support, ['supported', 'unsupported'], true)) {
            $errors[] = $prefix . 'support must be one of "supported", "unsupported"';
        }

        $this->requireKnownSources($rule['sources'] ?? [], $sourceIds, $prefix, $errors);

        if (isset($rule['recommended']) && !is_array($rule['recommended'])) {
            $errors[] = $prefix . 'recommended must be an object when present';
        }
    }

    private function validateMigration(mixed $migration, int $index, array &$errors): void
    {
        if (!is_array($migration)) {
            $errors[] = "migrations[$index] must be an object";
            return;
        }

        $prefix = "migrations[$index].";
        $this->requireString($migration, 'from', $errors, $prefix);
        $this->requireString($migration, 'to', $errors, $prefix);
        $this->requireArray($migration, 'applicableVersions', $errors, $prefix);
        $this->requireString($migration, 'guideUrl', $errors, $prefix);
    }

    private function requireKnownSources(mixed $ids, array $knownSourceIds, string $prefix, array &$errors): void
    {
        if (!is_array($ids)) {
            $errors[] = $prefix . 'sources must be an array of source ids';
            return;
        }

        foreach ($ids as $id) {
            if (!in_array($id, $knownSourceIds, true)) {
                $errors[] = $prefix . "sources references unknown source id \"$id\"";
            }
        }
    }

    private function requireString(array $data, string $key, array &$errors, string $prefix = ''): void
    {
        if (!isset($data[$key]) || !is_string($data[$key]) || $data[$key] === '') {
            $errors[] = $prefix . $key . ' must be a non-empty string';
        }
    }

    private function requireArray(array $data, string $key, array &$errors, string $prefix = ''): void
    {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $errors[] = $prefix . $key . ' must be an array';
        }
    }
}
