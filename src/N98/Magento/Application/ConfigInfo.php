<?php
declare(strict_types=1);

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
