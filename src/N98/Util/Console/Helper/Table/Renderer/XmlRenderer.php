<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

use DOMDocument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class XmlRenderer
 * @package N98\Util\Console\Helper\Table\Renderer
 */
class XmlRenderer implements RendererInterface
{
    /**
     * @param OutputInterface $output
     * @param array           $rows
     */
    public function render(OutputInterface $output, array $rows)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $rootXml = $dom->createElement('table');
        $dom->appendChild($rootXml);

        foreach ($rows as $row) {
            $rowXml = $dom->createElement('row');
            foreach ($row as $key => $value) {
                $cellXml = $dom->createElement($this->elementName($key));
                // Not createElement()'s second argument: that one is inserted unescaped, so a
                // value containing "&" or "<" - a label like "Terms & Conditions" - would either
                // be truncated at the ampersand or break the document.
                $cellXml->appendChild(
                    $dom->createTextNode((string) @iconv('UTF-8', 'UTF-8//IGNORE', (string) $value))
                );
                $rowXml->appendChild($cellXml);
            }
            $rootXml->appendChild($rowXml);
        }

        // Written raw: writeln() would otherwise run the document through Symfony's output
        // formatter, which treats any element whose name happens to match a registered style tag
        // - <info>, <comment>, <error>, <key>, <term>, ... - as markup and drops the tag,
        // leaving the cell's text unwrapped and the XML no longer well formed.
        $output->writeln($dom->saveXML(), OutputInterface::OUTPUT_RAW);
    }

    /**
     * Turn a column header into a usable XML element name.
     *
     * Headers are free-form English ("Updated At", "Scope-ID") and are occasionally numeric, so
     * beyond replacing the characters XML forbids we also have to guarantee the name does not
     * start with a digit - createElement() would throw for those.
     *
     * @param int|string $key
     */
    private function elementName($key): string
    {
        $name = preg_replace('/[^A-Za-z0-9]/u', '_', (string) $key);

        if ($name === '' || preg_match('/^[A-Za-z_]/', $name) !== 1) {
            $name = '_' . $name;
        }

        return $name;
    }
}
