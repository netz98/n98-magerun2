<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\Database\Compressor\AbstractCompressor;
use N98\Magento\Command\Database\Compressor\Uncompressed;

/**
 * Class ExecsTest
 *
 * @covers N98\Magento\Command\Database\Execs
 * @package N98\Magento\Command\Database
 */
class ExecsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $execs = new Execs();
        $this->assertInstanceOf(Execs::class, $execs);
    }

    /**
     * @test
     */
    public function facade()
    {
        $execs = new Execs('foo');
        $this->assertInstanceOf(Uncompressed::class, $execs->getCompressor());
        $execs->setCompression('gzip');
        $this->assertInstanceOf(AbstractCompressor::class, $execs->getCompressor());
        $this->assertNull($execs->getFileName());
        $execs->setFileName('output.sql');
        $this->assertNotNull($execs->getFileName());
        $this->assertSame('output.sql', $execs->getFileName());
        $this->assertSame('foo | gzip -c  > \'output.sql\'', $execs->getBaseCommand());
        $execs->addOptions(' --bar=box --flux ');
        $this->assertSame('foo --bar=box --flux | gzip -c  > \'output.sql\'', $execs->getBaseCommand());
        $this->assertCount(1, $execs->getCommands());
        $this->assertEquals(
            ['foo --bar=box --flux | gzip -c  > \'output.sql\''],
            $execs->getCommands()
        );
        $execs->add('--muxbux');
        $execs->add('--maxbax');
        $this->assertCount(2, $execs->getCommands());
        $this->assertEquals(
            [
                'foo --bar=box --flux --muxbux | gzip -c  > \'output.sql\'',
                'foo --bar=box --flux --maxbax | gzip -c  >> \'output.sql\'',
            ],
            $execs->getCommands()
        );
    }
}
