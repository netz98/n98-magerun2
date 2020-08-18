<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

class TimeElapsedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function invocation()
    {
        $string = TimeElapsed::full(0);
        $this->assertInternalType('string', $string);
    }

    public function provideCalculations()
    {
        return [
            ['just now', 0, 0],
            ['just now', 0, null],
            ['1 second ago', 1, 1],
            ['1 second ago', 1, null],
            ['2 seconds ago', 2, 2],
            ['2 seconds ago', 2, null],
            ['1 second ago', ' 2012-12-12T13:44:40Z', 1355319881],
            [
                '85 years, 10 months, 3 weeks, 1 day, 3 hours, 29 minutes, 21 seconds ago',
                ' 2012-12-12T13:44:40Z',
                -1355319881,
                '85 years ago',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideCalculations
     */
    public function fullAndShortCalculations($full, $datetimeOrSeconds, $now, $short = null)
    {
        $this->assertEquals($full, TimeElapsed::full($datetimeOrSeconds, $now));
        $this->assertEquals($short ?: $full, TimeElapsed::short($datetimeOrSeconds, $now));
    }

    /**
     * @test
     */
    public function negativeTimestampNotHandleable()
    {
        $this->expectException(\BadMethodCallException::class);
        // one second in the past at the beginng of time
        TimeElapsed::full(1, 0);
    }

    /**
     * @test
     */
    public function invalidDatetime()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DateTime::__construct():');
        TimeElapsed::full(' 0000----12T13:44:40Z', 0);
    }
}
