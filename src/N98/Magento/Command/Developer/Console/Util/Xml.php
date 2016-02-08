<?php

namespace N98\Magento\Command\Developer\Console\Util;

class Xml
{
    /**
     * @param string $xmlString
     *
     * @return string
     */
    public static function formatString($xmlString)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadXML($xmlString);

        return preg_replace('%(^\s*)%m', '$1$1', $dom->saveXml());
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param string $path
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     *
     * @link https://github.com/astorm/pestle
     * @copyright Pulse Storm LLC, Alan Storm
     *
     * @throws \Exception
     */
    public static function addSimpleXmlNodesByXPath(\SimpleXMLElement $xml, $path)
    {
        $path = trim($path, '/');
        $node = $xml;

        foreach (explode('/', $path) as $part) {
            $parts = explode('[', $part);
            $nodeName = array_shift($parts);
            $isNewNode = true;

            if (isset($node->{$nodeName})) {
                $isNewNode = false;
                $node = $node->{$nodeName};
            } else {
                $node = $node->addChild($nodeName);
            }

            $attributeString = trim(array_pop($parts), ']');

            if (!$attributeString) {
                continue;
            }

            $pairs = explode(',', $attributeString);

            foreach ($pairs as $pair) {
                if (!$isNewNode) {
                    continue;
                }

                list($key, $value) = explode('=', $pair);

                if(strpos($key, '@') !== 0) {
                    throw new \Exception("Invalid Attribute Key");
                }

                $key = trim($key, '@');
                if (strpos($key, ':') !== false) {
                    list($namespacePrefix, $rest) = explode(':', $key);
                    $namespace = self::getXmlNamespaceFromPrefix($xml, $namespacePrefix);
                    $node->addAttribute($key, $value, $namespace);
                } else {
                    $node->addAttribute($key, $value);
                }

            }
        }

        return $xml;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param string $prefix
     * @return string
     * @throws \Exception
     *
     * @link https://github.com/astorm/pestle
     * @copyright Pulse Storm LLC, Alan Storm
     */
    public static function getXmlNamespaceFromPrefix(\SimpleXMLElement $xml, $prefix)
    {
        $namespaces = $xml->getDocNamespaces();

        if(array_key_exists($prefix, $namespaces)) {
            return $namespaces[$prefix];
        }

        throw new \Exception('Unknown namespace in ' . __FILE__);
    }

}