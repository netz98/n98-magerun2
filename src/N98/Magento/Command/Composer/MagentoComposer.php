<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\Console\Application;

class MagentoComposer
{
    /**
     * @var Composer
     */
    private static $composer;

    /**
     * @param string $composerConfigFile
     * @return Composer
     * @throws \Composer\Json\JsonValidationException
     */
    public static function initBundledComposer(string $magentoRootDir)
    {
        if (! self::$composer instanceof Composer) {
            $composerApplication = new Application();
            $composerApplication->setAutoExit(false);

            $composer = $composerApplication->getComposer();
            $composer->setConfig(
                new Config(
                    true,
                    $magentoRootDir
                )
            );

            self::$composer = $composer;
        }

        return self::$composer;
    }
}
