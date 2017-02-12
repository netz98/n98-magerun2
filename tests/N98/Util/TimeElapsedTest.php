<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

class TimeElapsedTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array('just now', 0, 0),
            array('just now', 0, null),
            array('1 second ago', 1, 1),
            array('1 second ago', 1, null),
            array('2 seconds ago', 2, 2),
            array('2 seconds ago', 2, null),
            array('1 second ago', ' 2012-12-12T13:44:40Z', 1355319881),
            array(
                '85 years, 10 months, 3 weeks, 1 day, 3 hours, 29 minutes, 21 seconds ago',
                ' 2012-12-12T13:44:40Z',
                -1355319881,
                '85 years ago',
            ),
        );
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
     * @expectedException \BadMethodCallException
     */
    public function negativeTimestampNotHandleable()
    {
        // one second in the past at the beginng of time
        TimeElapsed::full(1, 0);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage DateTime::__construct():
     */
    public function invalidDatetime()
    {
        TimeElapsed::full(' 0000----12T13:44:40Z', 0);
    }
}
