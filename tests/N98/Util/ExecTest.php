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

    /**
     * @test
     */
    public function wrapWithBashPipefailWrapsWhenBashAvailable()
    {
        $bash = trim((string) shell_exec('command -v bash 2>/dev/null'));
        if (!$bash) {
            $this->markTestSkipped('bash not available');
        }

        $wrapped = Exec::wrapWithBashPipefail('echo hello');
        $this->assertStringContainsString('bash', $wrapped);
        $this->assertStringContainsString('set -o pipefail', $wrapped);
        $this->assertStringContainsString('echo hello', $wrapped);
    }

    /**
     * @test
     */
    public function wrapWithBashPipefailHandlesSingleQuotes()
    {
        $bash = trim((string) shell_exec('command -v bash 2>/dev/null'));
        if (!$bash) {
            $this->markTestSkipped('bash not available');
        }

        $command = "echo 'hello world'";
        $wrapped = Exec::wrapWithBashPipefail($command);

        // The wrapped command must be executable and produce the right output
        exec($wrapped, $out, $rc);
        $this->assertSame(0, $rc);
        $this->assertSame('hello world', $out[0]);
    }

    /**
     * @test
     */
    public function wrapWithBashPipefailPropagatesPipeError()
    {
        $bash = trim((string) shell_exec('command -v bash 2>/dev/null'));
        if (!$bash) {
            $this->markTestSkipped('bash not available');
        }

        // false is a command that always exits with code 1
        $wrapped = Exec::wrapWithBashPipefail('false | echo ok');
        exec($wrapped . ' 2>/dev/null', $out, $rc);
        $this->assertNotSame(0, $rc, 'pipe failure should propagate a non-zero exit code');
    }
}
