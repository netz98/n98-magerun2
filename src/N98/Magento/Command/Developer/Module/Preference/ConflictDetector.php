<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Preference;

use DOMDocument;
use DOMXPath;
use FilesystemIterator;
use RuntimeException;

class ConflictDetector
{
    /**
     * Inspect unmerged declarations, keeping global and each area separate.
     *
     * @param array<string, string> $modulePaths Enabled module names and registered paths
     * @return array<int, array{scope: string, target: string, type: string, module: string, file: string, line: int}>
     */
    public function detect(array $modulePaths, ?string $area = null): array
    {
        if ($area !== null && !preg_match('/^[a-zA-Z0-9_]+$/D', $area)) {
            throw new RuntimeException('Invalid area name: ' . $area);
        }

        $groups = [];
        foreach ($modulePaths as $module => $path) {
            if (!is_dir($path) || !is_readable($path)) {
                throw new RuntimeException('Cannot read registered module directory: ' . $path);
            }
            $configDirectory = $path . '/etc';
            if (!file_exists($configDirectory)) {
                continue;
            }
            if (!is_dir($configDirectory) || !is_readable($configDirectory)) {
                throw new RuntimeException('Cannot read module configuration directory: ' . $configDirectory);
            }
            $files = [];
            if ($area === null || $area === 'global') {
                if (file_exists($path . '/etc/di.xml')) {
                    $files[$path . '/etc/di.xml'] = 'global';
                }
            }
            if ($area !== 'global') {
                foreach (new FilesystemIterator($configDirectory) as $entry) {
                    $scope = $entry->getFilename();
                    if (!$entry->isDir() || ($area !== null && $scope !== $area) || $scope === 'global') {
                        continue;
                    }
                    if (!$entry->isReadable()) {
                        throw new RuntimeException('Cannot read module area directory: ' . $entry->getPathname());
                    }
                    // etc/global/di.xml is not Magento's global module configuration.
                    $file = $entry->getPathname() . '/di.xml';
                    if (file_exists($file)) {
                        $files[$file] = $scope;
                    }
                }
            }
            foreach ($files as $file => $scope) {
                foreach ($this->readPreferences($file) as $preference) {
                    $groups[$scope][$preference['target']][] = [
                        'scope' => $scope,
                        'target' => $preference['target'],
                        'type' => $preference['type'],
                        'module' => $module,
                        'file' => $file,
                        'line' => $preference['line'],
                    ];
                }
            }
        }

        $rows = [];
        foreach ($groups as $targets) {
            foreach ($targets as $declarations) {
                if (count(array_unique(array_column($declarations, 'type'))) > 1) {
                    foreach ($declarations as $declaration) {
                        $rows[] = $declaration;
                    }
                }
            }
        }
        usort($rows, static function (array $left, array $right): int {
            foreach (['scope', 'target', 'type', 'module', 'file'] as $key) {
                $comparison = strcmp($left[$key], $right[$key]);
                if ($comparison !== 0) {
                    return $comparison;
                }
            }
            return $left['line'] <=> $right['line'];
        });

        return $rows;
    }

    /**
     * @return array<int, array{target: string, type: string, line: int}>
     */
    private function readPreferences(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException('Cannot read DI file: ' . $file);
        }
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new RuntimeException('Cannot read DI file: ' . $file);
        }
        if ($contents === '') {
            throw new RuntimeException('Invalid DI XML: ' . $file);
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            // Never load DTDs, substitute entities, or allow network access.
            if (!$document->loadXML($contents, LIBXML_NONET) || $document->documentElement->tagName !== 'config') {
                throw new RuntimeException('Invalid DI XML: ' . $file);
            }
            if ($document->doctype !== null) {
                throw new RuntimeException('DOCTYPE is not allowed in DI XML: ' . $file);
            }
            $preferences = [];
            foreach ((new DOMXPath($document))->query('/config/preference') as $node) {
                $target = trim($node->getAttribute('for'));
                $type = trim($node->getAttribute('type'));
                if ($target === '' || $type === '') {
                    throw new RuntimeException('Preference requires non-empty for and type attributes: ' . $file);
                }
                $preferences[] = ['target' => $target, 'type' => $type, 'line' => $node->getLineNo()];
            }

            return $preferences;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
