<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Magento\Application;

use N98\Magento\Command\TestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\NullOutput;

class ConfigurationLoaderTest extends TestCase
{
    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationLoader = new ConfigurationLoader([], false, new NullOutput());
    }

    /**
     * @test
     */
    public function creation()
    {
        $this->assertInstanceOf(__NAMESPACE__ . '\\ConfigurationLoader', $this->configurationLoader);
    }

    /**
     * This method is executed during init process
     *
     * @see \N98\Magento\Application::init
     * @test
     */
    public function loadPartialConfig()
    {
        // without external config
        $data = $this->configurationLoader->getPartialConfig(false);

        $this->assertArrayHasKey('application', $data);
        $this->assertArrayHasKey('plugin', $data);
        $this->assertArrayHasKey('helpers', $data);
        $this->assertArrayHasKey('script', $data);
        $this->assertArrayHasKey('init', $data);
        $this->assertArrayHasKey('detect', $data);
        $this->assertArrayHasKey('event', $data);
        $this->assertArrayHasKey('commands', $data);
    }

    /**
     * @test
     */
    public function loadStageTwo()
    {
        $this->getApplication()->setMagentoRootFolder(vfsStream::url('root'));
        $this->configurationLoader->loadStageTwo(null, true, '');

        $data = $this->configurationLoader->toArray();

        $this->assertArrayHasKey('application', $data);
        $this->assertArrayHasKey('plugin', $data);
        $this->assertArrayHasKey('helpers', $data);
        $this->assertArrayHasKey('script', $data);
        $this->assertArrayHasKey('init', $data);
        $this->assertArrayHasKey('detect', $data);
        $this->assertArrayHasKey('event', $data);
        $this->assertArrayHasKey('commands', $data);
    }
}
