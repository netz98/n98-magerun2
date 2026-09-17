#!/usr/bin/env php
<?php
/**
 * Validates one or more db-compatibility dataset files against
 * N98\Util\Compatibility\Dataset\DatasetSchemaValidator.
 *
 * Usage:
 *   php validate-dataset.php [--project-root=/path/to/n98-magerun2] [file ...]
 *
 * Defaults to validating ./res/db-compatibility.json relative to --project-root
 * (or the current working directory) when no files are given.
 *
 * Exits 0 and prints the dataset revision when valid; exits 1 and prints every
 * validation error when invalid.
 */

$projectRoot = getcwd();
$files = [];

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--project-root=')) {
        $projectRoot = rtrim(substr($arg, strlen('--project-root=')), '/');
        continue;
    }
    $files[] = $arg;
}

if ($files === []) {
    $files = [$projectRoot . '/res/db-compatibility.json'];
}

$autoload = $projectRoot . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Could not find $autoload - pass --project-root pointing at the n98-magerun2 checkout, and make sure `composer install` has run.\n");
    exit(1);
}

require $autoload;

$validator = new N98\Util\Compatibility\Dataset\DatasetSchemaValidator();
$exitCode = 0;

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "$file: file not found\n");
        $exitCode = 1;
        continue;
    }

    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        fwrite(STDERR, "$file: not valid JSON\n");
        $exitCode = 1;
        continue;
    }

    $errors = $validator->validate($data);
    if ($errors === []) {
        $revision = $data['datasetRevision'] ?? 'unknown';
        $ruleCount = count($data['rules'] ?? []);
        $appCount = count($data['applications'] ?? []);
        echo "$file: OK (revision $revision, {$appCount} application scopes, {$ruleCount} rules)\n";
        continue;
    }

    fwrite(STDERR, "$file: INVALID\n");
    foreach ($errors as $error) {
        fwrite(STDERR, "  - $error\n");
    }
    $exitCode = 1;
}

exit($exitCode);
