<?php
/**
 * this file is part of magerun-shared
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
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
    public static function createApplication(ClassLoader $loader = null)
    {
        if (null === $loader) {
            $loader = self::getLoader();
        }

        // @lnk https://github.com/humbug/php-scoper/issues/298 
        $GLOBALS['__composer_autoload_files'] = [
            /* vendor/guzzlehttp/psr7/src/functions_include.php */
            'a0edc8309cc5e1d60e3047b5df6b7052' => false,
            /* vendor/guzzlehttp/guzzle/src/functions_include.php */
            '37a3dc5111fe8f707ab4c132ef1dbc62' => false,
        ];

        $application = new Magento\Application($loader);

        return $application;
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
