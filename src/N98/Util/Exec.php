<?php

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

        if (self::isPipefailOptionAvailable()) {
            $command = self::SET_O_PIPEFAIL . $command;
        }

        $command .= self::REDIRECT_STDERR_TO_STDOUT;

        // Temporarily suppress E_WARNING for exec, as MySQL might output password warnings to stderr
        $oldErrorReporting = error_reporting();
        error_reporting($oldErrorReporting & ~E_WARNING);

        exec($command, $outputArray, $returnCode);

        error_reporting($oldErrorReporting);

        // Filter out MySQL password warnings
        $filteredOutputArray = [];
        foreach ((array) $outputArray as $line) {
            if (strpos($line, "Using a password on the command line interface can be insecure") === false) {
                $filteredOutputArray[] = $line;
            }
        }
        $outputArray = $filteredOutputArray;

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
     * @return bool
     */
    private static function isPipefailOptionAvailable()
    {
        // Temporarily suppress E_WARNING for exec
        $oldErrorReporting = error_reporting();
        error_reporting($oldErrorReporting & ~E_WARNING);

        exec('set -o | grep pipefail 2>&1', $output, $returnCode);

        error_reporting($oldErrorReporting);

        return $returnCode == self::CODE_CLEAN_EXIT;
    }
}
