#!/usr/bin/env php
<?php
/**
 * Refreshes res/db-compatibility.json from magento.watch (https://magento.watch/api), an
 * independent, open, no-auth community API tracking exact per-release MySQL/MariaDB requirements
 * for Magento Open Source, Adobe Commerce, and Mage-OS.
 *
 * This is a deterministic, reviewable data refresh - it only ever touches entries it generated on
 * a previous run (see N98\Util\Compatibility\Import\MagentoWatchDatasetImporter::ID_PREFIX);
 * hand-authored rules, migrations[] guidance, and freshnessPolicy are left untouched. Review the
 * result the normal way, via `git diff`.
 *
 * Usage:
 *   php scripts/import-db-compatibility-dataset.php [--project-root=/path] [--distributions=a,b,c] [--dry-run] [--timeout=15]
 *
 * Defaults to updating ./res/db-compatibility.json relative to --project-root (or the current
 * working directory), for all three distributions.
 */

$projectRoot = getcwd();
$distributions = null;
$dryRun = false;
$timeout = 15;

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--project-root=')) {
        $projectRoot = rtrim(substr($arg, strlen('--project-root=')), '/');
    } elseif (str_starts_with($arg, '--distributions=')) {
        $distributions = array_filter(array_map('trim', explode(',', substr($arg, strlen('--distributions=')))));
    } elseif (str_starts_with($arg, '--timeout=')) {
        $timeout = (int) substr($arg, strlen('--timeout='));
    } elseif ($arg === '--dry-run') {
        $dryRun = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        fwrite(
            STDOUT,
            <<<HELP
Usage: php import-db-compatibility-dataset.php [options]

  --project-root=PATH     n98-magerun2 checkout to update (default: current directory)
  --distributions=A,B,C   magento.watch distributions to fetch
                           (default: magento-community,magento-commerce,mage-os)
  --timeout=SECONDS       per-request timeout (default: 15)
  --dry-run               fetch, transform, and validate, but do not write the file

HELP
        );
        exit(0);
    } else {
        fwrite(STDERR, "Unknown argument: $arg\n");
        exit(1);
    }
}

$autoload = $projectRoot . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Could not find $autoload - pass --project-root pointing at the n98-magerun2 checkout, and make sure `composer install` has run.\n");
    exit(1);
}

require $autoload;

use N98\Util\Compatibility\Dataset\DatasetSchemaValidator;
use N98\Util\Compatibility\Import\MagentoWatchClient;
use N98\Util\Compatibility\Import\MagentoWatchDatasetImporter;

$datasetPath = $projectRoot . '/res/db-compatibility.json';
if (!is_file($datasetPath)) {
    fwrite(STDERR, "$datasetPath not found.\n");
    exit(1);
}

$existingDataset = json_decode((string) file_get_contents($datasetPath), true);
if (!is_array($existingDataset)) {
    fwrite(STDERR, "$datasetPath is not valid JSON.\n");
    exit(1);
}

$client = new MagentoWatchClient();
$fetchDistributions = $distributions ?? MagentoWatchClient::ALL_DISTRIBUTIONS;

fwrite(STDOUT, 'Fetching ' . implode(', ', $fetchDistributions) . " from magento.watch...\n");

try {
    $responses = $client->fetchAll($fetchDistributions, $timeout);
} catch (\Throwable $e) {
    fwrite(STDERR, 'Fetch failed: ' . $e->getMessage() . "\n");
    fwrite(STDERR, "res/db-compatibility.json was not modified.\n");
    exit(1);
}

foreach ($responses as $distribution => $versions) {
    fwrite(STDOUT, sprintf("  %s: %d version(s)\n", $distribution, count($versions)));
}

$importer = new MagentoWatchDatasetImporter();
$result = $importer->import($existingDataset, $responses);

$validator = new DatasetSchemaValidator();
$errors = $validator->validate($result->dataset());
if ($errors !== []) {
    fwrite(STDERR, "Generated dataset failed schema validation - not writing anything:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, "  - $error\n");
    }
    exit(1);
}

fwrite(STDOUT, "\n" . $result->summary() . "\n");

if (!$result->hasChanges()) {
    fwrite(STDOUT, "No changes - res/db-compatibility.json is already current.\n");
    exit(0);
}

foreach (['addedRuleIds' => 'Added', 'updatedRuleIds' => 'Updated', 'removedRuleIds' => 'Removed'] as $method => $label) {
    $ids = $result->$method();
    if ($ids !== []) {
        fwrite(STDOUT, "$label:\n");
        foreach ($ids as $id) {
            fwrite(STDOUT, "  - $id\n");
        }
    }
}

if ($dryRun) {
    fwrite(STDOUT, "\n--dry-run given, not writing res/db-compatibility.json.\n");
    exit(0);
}

$json = json_encode($result->dataset(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
file_put_contents($datasetPath, $json);

fwrite(STDOUT, "\nWrote $datasetPath (revision {$result->dataset()['datasetRevision']}).\n");
fwrite(STDOUT, "Review with `git diff res/db-compatibility.json`, then run scripts/validate-db-compatibility-dataset.php and the test suite.\n");
