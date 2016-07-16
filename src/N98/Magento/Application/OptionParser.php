<?php
/**
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

/**
 * Ad-hoc options parser, used to deal with shortcoming of getopt() with Symfony style command-line options, arguments
 * and
 */
final class OptionParser
{
    private $argv;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    /**
     * @param array $argv [optional]
     * @return OptionParser
     */
    public static function init(array $argv = null)
    {
        if (null === $argv) {
            $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
        }

        return new self($argv);
    }

    /**
     * Check for long-opt option existence
     *
     * @param string $name of long option (e.g. root-dir for --root-dir)
     * @return null|true
     */
    public function hasLongOption($name)
    {
        $long = "--$name";

        $args = $this->argv;

        // utility
        array_shift($args);

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
            if ($long === $option) {
                return true;
            }
        }

        return null;
    }

    /**
     * Obtain the non-optional argument of a long-opt
     *
     * Note: This method does not differ between a non-existent option
     *       or an option with no argument or an option whichs
     *       argument is missing and then taking the next argument as
     *       the options' argument.
     *
     * @param string $name of long option (e.g. root-dir for --root-dir)
     * @return null|string option argument if it exists, otherwise null
     */
    public function getLongOptionArgument($name)
    {
        $long = "--$name";

        $args = $this->argv;

        // utility
        array_shift($args);

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
            if ($long === $option) {
                if (null !== $argument = array_shift($args)) {
                    $path = $argument;
                }
                break;
            }
            if ($long . '=' === substr($option, 0, $optLen = strlen($long) + 1)) {
                if ($len > $optLen) {
                    $path = substr($option, $optLen);
                }
                break;
            }
        }

        return isset($path) ? $path : null;
    }
}
