<?php
/**
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application\Option;


/**
 * --root-dir option needs a specialized parser b/c despite defined with
 * the symfony console API, it's not usable at early stages and
 * getopt() fails as well.
 */
class RootDir
{
    /**
     * @param array $argv (optional)
     *
     * @return null|string --root-dir option argument if it exists, otherwise null
     */
    public static function getArgument(array $argv = null)
    {
        if (null === $argv) {
            $argv = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : array();
        }

        $args = $argv;
        $utility = array_shift($args);
        unset($utility);

        while ($option = array_shift($args)) {
            if ('--' === $option) {
                break;
            }
            $len = strlen($option);
            if (!$len) {
                continue;
            }
            if ('-' !== $option[0]) {
                continue;
            }
            if ('--root-dir' === $option) {
                if (null !== $argument = array_shift($args)) {
                    $path = $argument;
                    break;
                }
            }
            if ('--root-dir=' === substr($option, 0, 11)) {
                if ($len > 11) {
                    $path = substr($option, 11);
                }
                break;
            }
        }

        return isset($path) ? $path : null;
    }
}
