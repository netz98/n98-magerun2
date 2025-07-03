<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console\Util\Config;

use N98\Magento\Command\Developer\Console\Util\Xml;

/**
 * Class DiFileWriter
 * @package N98\Magento\Command\Developer\Console\Util\Config
 */
class DiFileWriter extends FileWriter
{
    /**
     * @var string
     */
    protected static $defaultXml = <<<'XML'
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
</config>
XML;

    /**
     * @param string $commandName
     * @param string $commandClass
     *
     * @return DiFileWriter|null
     */
    public function addConsoleCommand($commandName, $commandClass)
    {
        /*<type name="">
            <arguments>
                <argument name="commands" xsi:type="array">
                    <item name="$name$Command" xsi:type="object">$class$</item>
                </argument>
            </arguments>
        </type>*/

        $xpath = new \DOMXPath($this);

        $commandAlreadyExistsQuery = $xpath->query("//item[text()='$commandClass']");
        if ($commandAlreadyExistsQuery->length > 0) {
            return null;
        }

        $argumentElementQuery = $xpath->query(
            '//type[@name="Magento\Framework\Console\CommandList"]/arguments/argument'
        );
        if ($argumentElementQuery->length > 0) {
            $argumentElement = $argumentElementQuery->item(0);
        } else {
            $typeElement = $this->createElement('type');
            $typeElement->setAttribute('name', 'Magento\Framework\Console\CommandList');

            $argumentsElement = $this->createElement('arguments');
            $typeElement->appendChild($argumentsElement);

            $argumentElement = $this->createElement('argument');
            $argumentElement->setAttribute('name', 'commands');
            $argumentElement->setAttribute('xsi:type', 'array');
            $argumentsElement->appendChild($argumentElement);

            $this->documentElement->appendChild($typeElement);
        }

        $itemElement = $this->createElement('item', $commandClass);
        $itemElement->setAttribute('name', $commandName);
        $itemElement->setAttribute('xsi:type', 'object');
        $argumentElement->appendChild($itemElement);

        return $this;
    }

    public function saveFile(string $filename)
    {
        $result = parent::save($filename);
        if (false === $result) {
            return false;
        }

        if ($result === 0) {
            return 0;
        }

        $buffer = file_get_contents($filename);
        $formattedXml = Xml::formatString($buffer);

        return file_put_contents($filename, $formattedXml);
    }
}
