<?php

namespace N98\Util;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @param string $string
     * @param string $delimiter
     * @param array  $expected
     * @dataProvider trimExplodeEmptyProvider
     */
    public function trimExplodeEmpty($delimiter, $string, $expected)
    {
        $this->assertEquals($expected, StringUtil::trimExplodeEmpty($delimiter, $string), '', 0.0, 10, true);
    }

    /**
     * @return array
     */
    public static function trimExplodeEmptyProvider()
    {
        return array(
            array(
                ',',
                'Foo,Bar',
                array('Foo', 'Bar')
            ),
            array(
                '#',
                ' Foo# Bar',
                array('Foo', 'Bar')
            ),
            array(
                ',',
                ',,Foo, Bar,,',
                array('Foo', 'Bar')
            ),
        );
    }

    /**
     * @test
     */
    public function startsWith()
    {
        $this->assertTrue(StringUtil::startsWith('Foo', 'Foo'));
        $this->assertTrue(StringUtil::startsWith('Foo123', 'Foo'));
        $this->assertFalse(StringUtil::startsWith(' Foo123', 'Foo'));
    }

    /**
     * @test
     */
    public function endsWith()
    {
        $this->assertTrue(StringUtil::endsWith('Foo', 'Foo'));
        $this->assertTrue(StringUtil::endsWith('Foo123', '123'));
        $this->assertFalse(StringUtil::endsWith(' Foo123 ', '123'));
    }
}