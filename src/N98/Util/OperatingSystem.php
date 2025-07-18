<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

/**
 * Class OperatingSystem
 *
 * @package N98\Util
 */
class OperatingSystem
{
    /**
     * @var int
     */
    const UID_ROOT = 0;

    /**
     * Returns true if operating system is
     * based on GNU linux.
     *
     * @return boolean
     */
    public static function isLinux()
    {
        return (bool) stristr(PHP_OS, 'linux');
    }

    /**
     * Returns true if operating system is
     * based on Microsoft Windows.
     *
     * @return boolean
     */
    public static function isWindows()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }

    /**
     * Returns true if operating system is
     * based on novell netware.
     *
     * @return boolean
     */
    public static function isNetware()
    {
        return (bool) stristr(PHP_OS, 'netware');
    }

    /**
     * Returns true if operating system is
     * based on apple MacOS.
     *
     * @return boolean
     */
    public static function isMacOs()
    {
        return stristr(PHP_OS, 'darwin') || stristr(PHP_OS, 'mac');
    }

    /**
     * @param string $program
     * @return bool
     */
    public static function isProgramInstalled($program)
    {
        if (self::isWindows()) {
            return WindowsSystem::isProgramInstalled($program);
        }

        return '' !== self::locateProgram($program);
    }

    /**
     * Returns the absolute path to the program that should be located or an empty string if the programm
     * could not be found.
     *
     * @param string $program
     * @return string
     */
    public static function locateProgram($program)
    {
        if (self::isWindows()) {
            return WindowsSystem::locateProgram($program);
        }

        $out = null;
        $return = null;
        @exec('which ' . $program, $out, $return);
        return ($return === 0 && isset($out[0])) ? $out[0] : '';
    }

    /**
     * Home directory of the current user
     *
     * @return string|false false in case there is no environment variable related to the home directory
     */
    public static function getHomeDir()
    {
        if (self::isWindows()) {
            return getenv('USERPROFILE');
        }

        return getenv('HOME');
    }

    /**
     * Test for Root UID on a POSIX system if posix_getuid() is available.
     *
     * Returns false negatives if posix_getuid() is not available.
     *
     * @return bool
     */
    public static function isRoot()
    {
        return function_exists('posix_getuid') && posix_getuid() === self::UID_ROOT;
    }

    /**
     * get current working directory
     *
     * @return string the current working directory on success, or false on failure.
     */
    public static function getCwd()
    {
        return getcwd();
    }

    /**
     * Retrieve path to php binary
     *
     * @return string
     */
    public static function getPhpBinary()
    {
        // PHP_BINARY (>= php 5.4)
        if (defined('PHP_BINARY')) {
            return PHP_BINARY;
        }

        if (self::isWindows()) {
            return 'php';
        }

        return '/usr/bin/env php';
    }

    /**
     * Retrieve path to current php binary.
     *
     * @return string
     */
    public static function getCurrentPhpBinary()
    {
        if (isset($_SERVER['_'])) {
            return $_SERVER['_'];
        }

        return self::getPhpBinary();
    }

    /**
     * @deprecated 5.1.1 No longer used by internal code
     * @return bool
     */
    public static function isBashCompatibleShell()
    {
        return in_array(
            basename(getenv('SHELL')),
            ['bash', 'zsh']
        );
    }
}
