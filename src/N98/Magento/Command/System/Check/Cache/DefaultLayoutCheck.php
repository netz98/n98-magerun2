<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Cache;

use DOMDocument;
use DOMXPath;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleListInterface;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Finder\Finder;

class DefaultLayoutCheck implements SimpleCheck
{
    private ComponentRegistrar $registrar;

    private ModuleListInterface $moduleList;

    public function __construct(ComponentRegistrar $registrar, ModuleListInterface $moduleList)
    {
        $this->registrar = $registrar;
        $this->moduleList = $moduleList;
    }

    public function check(ResultCollection $results)
    {
        $issues = 0;
        foreach ($this->getLayoutFiles() as $file) {
            // Discovery does not guarantee the file is still present or readable.
            // Keep filesystem warnings out of machine-readable command output.
            $xml = @file_get_contents($file);
            $previous = libxml_use_internal_errors(true);
            try {
                $document = new DOMDocument();
                $loaded = $xml !== false && $xml !== '' && $document->loadXML($xml, LIBXML_NONET);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }

            if (!$loaded || $document->doctype !== null) {
                $results->createResult(
                    Result::STATUS_WARNING,
                    'Cannot safely scan layout XML: ' . OutputFormatter::escape($file)
                );
                $issues++;
                continue;
            }

            $xpath = new DOMXPath($document);
            foreach ($xpath->query('//block[@cacheable]') as $block) {
                // xs:boolean permits both false and 0, with whitespace collapsed.
                if (!in_array(trim($block->getAttribute('cacheable')), ['false', '0'], true)) {
                    continue;
                }

                $identity = $block->getAttribute('name');
                if ($identity === '') {
                    $identity = '(unnamed)';
                }
                $results->createResult(
                    Result::STATUS_ERROR,
                    sprintf(
                        'Uncacheable block "%s" in frontend default layout %s:%d (cacheable="%s").',
                        OutputFormatter::escape($identity),
                        OutputFormatter::escape($file),
                        $block->getLineNo(),
                        OutputFormatter::escape($block->getAttribute('cacheable'))
                    )
                );
                $issues++;
            }
        }

        if ($issues === 0) {
            $results->createResult(Result::STATUS_OK, 'No uncacheable blocks found in frontend default layout declarations (static scan).');
        }
    }

    /**
     * Registered components include Composer packages outside app/code and app/design.
     * Theme overrides are declarations too; this deliberately does not merge layouts.
     *
     * @return string[]
     */
    private function getLayoutFiles(): array
    {
        $files = [];
        $modules = $this->registrar->getPaths(ComponentRegistrar::MODULE);
        foreach ($this->moduleList->getNames() as $module) {
            if (!isset($modules[$module])) {
                continue;
            }
            foreach (['base', 'frontend'] as $area) {
                $file = $modules[$module] . '/view/' . $area . '/layout/default.xml';
                if (is_file($file)) {
                    $files[$file] = $file;
                }
            }
        }

        foreach ($this->registrar->getPaths(ComponentRegistrar::THEME) as $name => $path) {
            if (strpos($name, 'frontend/') !== 0) {
                continue;
            }
            foreach ($this->moduleList->getNames() as $module) {
                $layout = $path . '/' . $module . '/layout';
                if (!is_dir($layout)) {
                    continue;
                }
                foreach (Finder::create()->files()->in($layout)->name('default.xml') as $file) {
                    if (!preg_match('#^(default\.xml|override/base/default\.xml|override/theme/[^/]+/[^/]+/default\.xml)$#', $file->getRelativePathname())) {
                        continue;
                    }
                    $files[$file->getPathname()] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }
}
