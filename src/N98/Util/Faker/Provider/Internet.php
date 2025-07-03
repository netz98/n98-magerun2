<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Faker\Provider;

/**
 * Class Internet
 * @package N98\Util\Faker\Provider
 */
class Internet extends \Faker\Provider\Internet
{
    /**
     * Reduce the chance of conflicts.
     *
     * @var array
     */
    protected static $userNameFormats = [
        '{{lastName}}.{{firstName}}.######',
        '{{firstName}}.{{lastName}}.######',
        '{{firstName}}.######',
        '?{{lastName}}.######',
    ];
}
