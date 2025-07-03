<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Route;

class ActionPathFormatter
{
    /**
     * Formats the path of an action (without module frontname)
     *
     * @param string $actionPath
     * @return string
     */
    public static function format(string $actionPath): string
    {
        return preg_replace('/_([^_]*)$/', '/$1', str_replace('/', '_', $actionPath));
    }
}
