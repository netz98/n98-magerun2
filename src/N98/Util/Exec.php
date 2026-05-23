<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

use RuntimeException;

/**
 * Class Exec
 *
 * @package N98\Util
 */
class Exec
{
    /**
     * @var string
     */
    const REDIRECT_STDERR_TO_STDOUT = ' 2>&1';

    /**
     * @var int (0-255)
     */
    const CODE_CLEAN_EXIT = 0;

    /**
     * Every error in a pipe will be exited with an error code
     */
    const SET_O_PIPEFAIL = 'set -o pipefail; ';

    /**
     * @var string|null Cached bash binary path; empty string means "not found"
     */
    private static $bashBinary = null;

    /**
     * @param string $command
     * @param string|null $output
     * @param int $returnCode
     */
    public static function run($command, &$output = null, &$returnCode = null)
    {
        if (!self::allowed()) {
            $message = sprintf("No PHP exec(), can not execute command '%s'.", $command);
            throw new RuntimeException($message);
        }

        $command = self::wrapWithBashPipefail($command);
        $command .= self::REDIRECT_STDERR_TO_STDOUT;

        exec($command, $outputArray, $returnCode);
        $output = self::parseCommandOutput((array) $outputArray);

        if ($returnCode !== self::CODE_CLEAN_EXIT) {
            throw new RuntimeException(
                sprintf("Exit status %d for command %s. Output was: %s", $returnCode, $command, $output)
            );
        }
    }

    /**
     * Exec class is allowed to run
     *
     * @return bool
     */
    public static function allowed()
    {
        return function_exists('exec');
    }

    /**
     * string from array of strings representing one line per entry
     *
     * @param array $commandOutput
     * @return string
     */
    private static function parseCommandOutput(array $commandOutput)
    {
        return implode(PHP_EOL, $commandOutput) . PHP_EOL;
    }

    /**
     * Wraps a command in an explicit bash sub-shell with pipefail enabled so that
     * errors in any segment of a pipeline propagate correctly even when /bin/sh is
     * dash (which does not support set -o pipefail).
     *
     * When bash is not available the command is returned unchanged.
     *
     * @param string $command
     * @return string
     */
    public static function wrapWithBashPipefail($command)
    {
        $bash = self::getBashBinary();
        if ($bash === null) {
            return $command;
        }

        return $bash . " -c 'set -o pipefail; " . self::escapeForBash($command) . "'";
    }

    /**
     * Returns the absolute path to bash, or null when bash is not available.
     * The result is cached for the lifetime of the PHP process.
     *
     * @return string|null
     */
    private static function getBashBinary()
    {
        if (self::$bashBinary === null) {
            $path = trim((string) shell_exec('command -v bash 2>/dev/null'));
            self::$bashBinary = $path ?: '';
        }

        return self::$bashBinary ?: null;
    }

    /**
     * Escapes a command string for safe embedding inside a bash single-quoted argument.
     * Every ' is replaced with '"'"' which ends the single-quoted string, appends a
     * double-quoted single quote, then reopens the single-quoted string.
     *
     * @param string $command
     * @return string
     */
    private static function escapeForBash($command)
    {
        return str_replace("'", "'\"'\"'", $command);
    }
}
