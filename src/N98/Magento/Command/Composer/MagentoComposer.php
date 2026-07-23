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
use Composer\Factory;
use Composer\IO\NullIO;

class MagentoComposer
{
    /**
     * @var Composer
     */
    private static $composer;

    /**
     * @param string $magentoRootDir
     * @return Composer
     * @throws \Composer\Json\JsonValidationException
     */
    public static function initBundledComposer(string $magentoRootDir)
    {
        if (! self::$composer instanceof Composer) {
            // Factory::create() forces plugins into 'local'-disabled mode whenever a non-default
            // composer.json path is passed in, so createComposer() is called directly with $cwd
            // set to $magentoRootDir instead, as documented in Factory::create()'s source.
            self::$composer = (new Factory())->createComposer(
                new NullIO(),
                $magentoRootDir . '/composer.json',
                false,
                $magentoRootDir
            );
        }

        return self::$composer;
    }
}
