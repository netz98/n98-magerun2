<?php

namespace N98\Magento\Command\Developer\Console\Util;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider providerAddSimpleXmlNodesByXPath
     *
     * @param string $xml
     * @param string $path
     * @param string $expected
     */
    public function addSimpleXmlNodesByXPath($xml, $path, $expected)
    {
        $xml = simplexml_load_string($xml);
        $xml = Xml::addSimpleXmlNodesByXPath($xml, $path);
        $this->assertContains($expected, $xml->asXML());
    }

    /**
     * @return array
     */
    public function providerAddSimpleXmlNodesByXPath()
    {
        return [
            'simple_path' => [
                '<config></config>',
                'title',
                '<config><title/></config>'
            ],
            'simple_path_with_leading_slash' => [
                '<config></config>',
                '/title',
                '<config><title/></config>'
            ],
            'complex_path_with_leading_slash' => [
                '<config></config>',
                '/title/bar',
                '<config><title><bar/></title></config>'
            ],
            'simple_path_with_attribute' => [
                '<config></config>',
                'title[@name=foo]',
                '<config><title name="foo"/></config>'
            ],
            'complex_path_with_attribute' => [
                '<config></config>',
                '/foo/title[@name=foo]/bar',
                '<config><foo><title name="foo"><bar/></title></foo></config>'
            ],
            'simple_path_with_namespace_attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd"></config>',
                'title[@xsi:type=string]',
                '<title xsi:type="string"/>'
            ],
            'complex_path_with_namespace_attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd"></config>',
                'foo/title[@xsi:type=string]/bar',
                '<foo><title xsi:type="string"><bar/></title></foo>'
            ],
            'simple_path_with_two_attributes' => [
                '<config></config>',
                'title[@name=foo,@name2=bar]',
                '<config><title name="foo" name2="bar"/></config>'
            ],
            'complex_path_with_two_attributes' => [
                '<config></config>',
                'foo/title[@name=foo,@name2=bar]/bar',
                '<config><foo><title name="foo" name2="bar"><bar/></title></foo></config>'
            ],
            'complex_path_with_multiple_two_attributes' => [
                '<config></config>',
                'foo/title[@name=foo,@name2=bar]/bar[@name=foo,@name2=bar]',
                '<config><foo><title name="foo" name2="bar"><bar name="foo" name2="bar"/></title></foo></config>'
            ],
        ];
    }
}