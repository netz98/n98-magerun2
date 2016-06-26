<?php
/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Magento\Application;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class ConfigurationLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $loader = new ConfigurationLoader([], false, new NullOutput());
        $this->assertInstanceOf(__NAMESPACE__ . '\\ConfigurationLoader', $loader);
    }
}
