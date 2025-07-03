<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

use RuntimeException;

/**
 * Class ExecTest
 *
 * @package N98\Util
 */
class ExecTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function commandOnly()
    {
        Exec::run('echo test');

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function fullParameters()
    {
        Exec::run('echo test', $commandOutput, $returnCode);

        $this->assertEquals(Exec::CODE_CLEAN_EXIT, $returnCode);
        $this->assertStringStartsWith('test', $commandOutput);
    }

    /**
     * @test
     */
    public function exception()
    {
        $this->expectException(RuntimeException::class);
        Exec::run('foobar');
    }
}
