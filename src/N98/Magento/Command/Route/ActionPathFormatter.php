<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
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
