<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

use InvalidArgumentException;
use N98\Magento\Command\TestCase;
use RuntimeException;

/**
 * Class ConfigFileTest
 *
 * @covers  N98\Magento\Application\ConfigFile
 * @package N98\Magento\Application
 */
class ConfigFileTest extends TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $configFile = new ConfigFile();
        $this->assertInstanceOf('\N98\Magento\Application\ConfigFile', $configFile);

        $configFile = ConfigFile::createFromFile(__FILE__);
        $this->assertInstanceOf('\N98\Magento\Application\ConfigFile', $configFile);
    }

    /**
     * @test
     */
    public function applyVariables()
    {
        $configFile = new ConfigFile();
        $configFile->loadFile('data://,- %root%');
        $configFile->applyVariables("root-folder");

        $this->assertSame(['root-folder'], $configFile->toArray());
    }

    /**
     * @test
     */
    public function mergeArray()
    {
        $configFile = new ConfigFile();
        $configFile->loadFile('data://,- bar');
        $result = $configFile->mergeArray(['foo']);

        $this->assertSame(['foo', 'bar'], $result);
    }

    /**
     * @test
     */
    public function parseEmptyFile()
    {
        $this->expectException(RuntimeException::class);
        $configFile = new ConfigFile();
        $configFile->loadFile('data://,');
        $this->addToAssertionCount(1);
        $configFile->toArray();
        $this->fail('An expected exception has not been thrown.');
    }

    /**
     * @test
     */
    public function invalidFileThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        @ConfigFile::createFromFile(":");
    }
}
