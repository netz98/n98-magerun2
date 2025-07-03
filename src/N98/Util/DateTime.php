<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

use DateTime as PhpDateTime;

/**
 * Class DateTime
 * @package N98\Util
 */
class DateTime
{
    /**
     * Returns a readable string with time difference
     *
     * @param PhpDateTime $time1
     * @param PhpDateTime $time2
     *
     * @return string
     */
    public function getDifferenceAsString(PhpDateTime $time1, PhpDateTime $time2)
    {
        if ($time1 == $time2) {
            return '0';
        }

        $interval = $time1->diff($time2);
        $years = $interval->format('%y');
        $months = $interval->format('%m');
        $days = $interval->format('%d');
        $hours = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%s');

        $differenceString
            = ($years ? $years . 'Y ' : '')
            . ($months ? $months . 'M ' : '')
            . ($days ? $days . 'd ' : '')
            . ($hours ? $hours . 'h ' : '')
            . ($minutes ? $minutes . 'm ' : '')
            . ($seconds ? $seconds . 's ' : '');

        return trim($differenceString);
    }
}
