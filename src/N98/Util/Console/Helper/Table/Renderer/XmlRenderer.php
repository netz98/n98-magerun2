<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper\Table\Renderer;

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
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $rootXml = $dom->createElement('table');
        $dom->appendChild($rootXml);

        foreach ($rows as $row) {
            $rowXml = $dom->createElement('row');
            foreach ($row as $key => $value) {
                $key = preg_replace('/[^A-Za-z0-9]/u', '_', $key);
                $rowXml->appendChild($dom->createElement($key, @iconv('UTF-8', 'UTF-8//IGNORE', $value)));
            }
            $rootXml->appendChild($rowXml);
        }

        $output->writeln($dom->saveXML());
    }
}
