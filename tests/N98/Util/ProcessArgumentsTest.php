<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ProcessArgumentsTest
 *
 * @covers \N98\Util\ProcessArguments
 * @package N98\Util
 */
class ProcessArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $args = new ProcessArguments();
        $this->assertInstanceOf(ProcessArguments::class, $args);
        $args = ProcessArguments::create();
        $this->assertInstanceOf(ProcessArguments::class, $args);
    }

    /**
     * @test
     */
    public function builderCreation()
    {
        $actual = ProcessArguments::create()->createBuilder();
        $this->assertInstanceOf(ProcessBuilder::class, $actual);
    }

    /**
     * @test
     */
    public function chaining()
    {
        $actual = ProcessArguments::create()
            ->addArg('command')
            ->addArgs(['-vvv', '--version-tricks-off', '--', '--' => true])
            ->addArg('--')
            ->addArgs(['-vvv', '--file' => 'music', '--empty' => true, 'flag' => true])
            ->createBuilder();
        $this->assertInstanceOf(ProcessBuilder::class, $actual);
        $commandLine = $actual->getProcess()->getCommandLine();
        $this->assertSame(
            "'command' '-vvv' '--version-tricks-off' '--' '--' '--' '-vvv' '--file=music' '--empty' '--flag'",
            $commandLine
        );
    }
}
