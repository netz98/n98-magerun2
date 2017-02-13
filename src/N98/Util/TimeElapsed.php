<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

use BadMethodCallException;
use DateInterval;

/**
 * Class TimeElapsed
 *
 * Diff a datetime against now and return human readable output (english) like
 *   1 year 40 weeks 2 days 1 hour 5 minutes 7 seconds ago
 * or
 *   just now
 *
 * @note borrowed from <http://stackoverflow.com/questions/1416697/>
 *
 * echo time_elapsed_string('2013-05-01 00:22:35');
 * echo time_elapsed_string('@1367367755'); # timestamp input
 * echo time_elapsed_string('2013-05-01 00:22:35', true);
 *
 * with changes:
 *  - extraced into static class methods
 *  - it is possible to provide the time of now (instead of time()
 *  - pass integers as seconds (relative to now)
 *
 * @package N98\Util
 */
class TimeElapsed
{
    /**
     * Full format, e.g. "85 years, 10 months, 3 weeks, 1 day, 3 hours, 29 minutes, 21 seconds ago"
     *
     * @param int|string $datetimeOrSeconds numbers of seconds (int) or \DateTime time (string)
     * @param int|null $now [optional] time()
     * @return string
     */
    public static function full($datetimeOrSeconds, $now = null)
    {
        $diff = self::diff($datetimeOrSeconds, $now);

        $pieces = self::pieces($diff);

        $buffer = $pieces ? implode(', ', $pieces) . ' ago' : 'just now';

        return $buffer;
    }

    /**
     * Short format, e.g. "85 years ago"
     *
     * @param int|string $datetimeOrSeconds numbers of seconds (int) or \DateTime time (string)
     * @param int|null $now [optional] time()
     * @return string
     */
    public static function short($datetimeOrSeconds, $now = null)
    {
        $diff = self::diff($datetimeOrSeconds, $now);

        $pieces = self::pieces($diff);

        $buffer = $pieces ? $pieces[0] . ' ago' : 'just now';

        return $buffer;
    }

    /**
     * @param int|string $datetimeOrSeconds numbers of seconds (int) or \DateTime time (string)
     * @param int|null $now [optional] time()
     * @return DateInterval
     */
    private static function diff($datetimeOrSeconds, $now = null)
    {
        $tsNow = $now === null ? time() : $now;

        if (is_numeric($datetimeOrSeconds)) {
            // seconds
            $timestampAgo = $tsNow - $datetimeOrSeconds;
            if ($timestampAgo < 0) {
                throw new BadMethodCallException(
                    sprintf('Negative unix timestamp with "%s" for now @ "%s"', $datetimeOrSeconds, $now)
                );
            }
            $dtStringAgo = "@$timestampAgo";
        } else {
            // datetime
            $dtStringAgo = $datetimeOrSeconds;
        }

        $now = new \DateTime("@$tsNow");
        $ago = new \DateTime($dtStringAgo);

        $diff = $now->diff($ago);

        if ($diff === false) {
            throw new BadMethodCallException(
                sprintf('Diff failed with "%s" for now @ "%s"', $datetimeOrSeconds, $now)
            );
        }

        return $diff;
    }

    /**
     * @param DateInterval $diff
     * @return array
     */
    private static function pieces(DateInterval $diff)
    {
        $keys = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        // map diff
        $diffArray = array();
        $diffVars = get_object_vars($diff);
        foreach ($keys as $unit => $name) {
            $diffArray[$unit] = isset($diffVars[$unit]) ? $diffVars[$unit] : null;
        }

        // shorten days by weeks (note: ignoring months and years)
        $weeks = floor($diffArray['d'] / 7);
        $diffArray['w'] = $weeks;
        $diffArray['d'] -= $weeks * 7;

        // fill string buffer array
        $pieces = array();
        foreach ($keys as $unit => $name) {
            if ($value = $diffArray[$unit]) {
                $pieces[] = sprintf('%s %s%s', $value, $name, $value > 1 ? 's' : '');
            }
        }

        return $pieces;
    }
}
