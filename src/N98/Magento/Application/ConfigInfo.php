<?php
declare(strict_types=1);
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Application;

class ConfigInfo
{
    public const TYPE_DIST = 'dist';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_USER = 'user';
    public const TYPE_PLUGIN = 'plugin';
    public const TYPE_PROJECT = 'project';

    public function __construct(public string $type, public string $path)
    {

    }
}
