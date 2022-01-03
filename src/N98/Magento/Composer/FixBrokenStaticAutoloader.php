<?php

declare(strict_types=1);

namespace N98\Magento\Composer;

use Composer\Script\Event;

/**
 * This fixes a php-scoper / box issue
 *
 * @link https://github.com/humbug/php-scoper/issues/298
 * @link https://github.com/phpro/grumphp-shim/blob/0b50cfc65bc475b2bc73f1565d3c43b44b2721f6/src/Composer/FixBrokenStaticAutoloader.php
 *
 * The result is that the "files" section in composer will be scoped as well.
 */
class FixBrokenStaticAutoloader
{
    public static function fix(Event $event)
    {
        $autoloadPath = __DIR__ . '/../../../../vendor/composer';

        $static_loader_path = $autoloadPath . '/autoload_static.php';
        $event->getIO()->write(["Fixing $static_loader_path"]);
        $static_loader = file_get_contents($static_loader_path);
        $static_loader = \preg_replace(
            '/\'([A-Za-z0-9]*?)\' => __DIR__ \. (.*?),/',
            '\'scoped$1\' => __DIR__ . $2,',
            $static_loader
        );
        file_put_contents($static_loader_path, $static_loader);

        $files_loader_path = $autoloadPath . '/autoload_files.php';
        $event->getIO()->write(["Fixing $files_loader_path"]);
        $files_loader = file_get_contents($files_loader_path);
        $files_loader = \preg_replace('/\'(.*?)\' => (.*?),/', '\'scoped$1\' => $2,', $files_loader);
        file_put_contents($files_loader_path, $files_loader);
    }
}
