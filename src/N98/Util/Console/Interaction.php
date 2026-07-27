<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console;

use N98\Util\OperatingSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Whether this invocation may take over the terminal to ask the user something.
 *
 * Interactive prompts are an enhancement, never a requirement: a command that would block waiting
 * for input it can never receive is worse than one that simply reports the argument it is missing.
 * Every caller that cannot answer - cron, CI, a pipeline, `--no-interaction` - has to be filtered
 * out before a prompt is drawn.
 */
final class Interaction
{
    /**
     * True when a laravel/prompts prompt can be drawn and answered.
     */
    public static function isPromptable(InputInterface $input, OutputInterface $output): bool
    {
        if (!$input->isInteractive() || !$output->isDecorated()) {
            return false;
        }

        if (Theme::colorDisabledByEnvironment()) {
            return false;
        }

        // laravel/prompts reads the terminal directly, so it needs a real tty and a platform it
        // supports. This mirrors ConfiguresPromptFallbacks::shouldFallbackToPlainPrompts().
        return !OperatingSystem::isWindows() && defined('STDIN') && stream_isatty(STDIN);
    }
}
