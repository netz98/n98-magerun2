<?php

namespace N98\Magento\Command\Developer\Console\Util\Config;

use N98\Magento\Command\Developer\Console\Util\Xml;

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
     * @return DiFileWriter
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
            return;
        }

        $argumentElementQuery = $xpath->query('//type[@name="Magento\Framework\Console\CommandList"]/arguments/argument');
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

    public function save($filename, $options = null)
    {
        if (parent::save($filename, $options)) {
            // Reformat
            $xml = file_get_contents($filename);
            $xml = Xml::formatString($xml);

            \file_put_contents($filename, $xml);
        }
    }


}
