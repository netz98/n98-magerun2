<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
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
