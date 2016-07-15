<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

use N98\Magento\Command\PHPUnit\TestCase;

/**
 * Class OptionParserTest
 *
 * @covers  N98\Magento\Application\OptionParser
 * @package N98\Magento\Application
 */
class OptionParserTest extends TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $parser = OptionParser::init(array());
        $this->assertInstanceOf('\N98\Magento\Application\OptionParser', $parser);
    }

    /**
     * @test
     */
    public function parseLongOptionArgument()
    {
        $parser = new OptionParser(array('--root-dir'));
        $this->assertNull($parser->getLongOptionArgument('root-dir'));

        $parser = new OptionParser(array('', '--root-dir'));
        $this->assertNull($parser->getLongOptionArgument('root-dir'));

        $parser = new OptionParser(array('', '--root-dir='));
        $this->assertNull($parser->getLongOptionArgument('root-dir'));

        $parser = new OptionParser(array('', '--root-dir=value'));
        $this->assertNotNull($parser->getLongOptionArgument('root-dir'));
        $this->assertSame('value', $parser->getLongOptionArgument('root-dir'));
    }

    /**
     * @test
     */
    public function hasLongOption()
    {
        $parser = new OptionParser(array('--root-dir'));
        $this->assertNull($parser->hasLongOption('root-dir'));

        $parser = new OptionParser(array('', '--root-dir'));
        $this->assertTrue($parser->hasLongOption('root-dir'));

        $parser = new OptionParser(array('', '--root-dir='));
        $this->assertNull($parser->hasLongOption('root-dir'));
    }
}
