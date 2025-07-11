<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98;

use Composer\Autoload\ClassLoader;
use ErrorException;

/**
 * Bootstrap class for the Magerun applications (Symfony console based application)
 *
 * @package N98
 */
class MagerunBootstrap
{
    /**
     * @param ClassLoader|null $loader [optional]
     * @return Magento\Application
     * @throws ErrorException
     */
    public static function createApplication(?ClassLoader $loader = null)
    {
        if (null === $loader) {
            $loader = self::getLoader();
        }

        return new Magento\Application($loader);
    }

    /**
     * @throws ErrorException
     * @return ClassLoader
     */
    public static function getLoader()
    {
        $projectBasedir = __DIR__ . '/../..';
        if (
            !($loader = self::includeIfExists($projectBasedir . '/vendor/autoload.php'))
            && !($loader = self::includeIfExists($projectBasedir . '/../../autoload.php'))
        ) {
            throw new ErrorException(
                'You must set up the project dependencies, run the following commands:' . PHP_EOL .
                'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
                'php composer.phar install' . PHP_EOL
            );
        }

        return $loader;
    }

    /**
     * @param string $file
     * @return mixed
     */
    public static function includeIfExists($file)
    {
        if (file_exists($file)) {
            return include $file;
        }
    }
}
