<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Builds every progress bar magerun shows, so long-running commands all look the same.
 *
 * Symfony's ProgressBar is used rather than laravel/prompts because it writes to the
 * OutputInterface it is handed - laravel/prompts renders to its own global stream - it throttles
 * and degrades on its own when the output is not decorated, and it can be driven by CommandTester.
 *
 * Undecorated output gets none of the styling: Symfony's own default format is left in place, which
 * emits one plain line per redraw instead of rewriting a single one - what cron mails and CI logs
 * want. The three hand-rolled bars this replaced each had their own plain format, so there was no
 * single previous rendering to preserve; Symfony's default is the one we can keep in step.
 */
final class ProgressFactory
{
    /**
     * Redraw at most this often on a terminal. Without it a fast loop spends its time on ANSI.
     */
    private const REDRAW_INTERVAL_SECONDS = 0.1;

    public static function create(OutputInterface $output, int $max = 0, string $label = ''): ProgressBar
    {
        $progress = new ProgressBar($output, $max);

        if (!self::isStyled($output)) {
            if ($label !== '') {
                $output->writeln($label);
            }

            return $progress;
        }

        $progress->setBarCharacter('<accent>' . Glyph::get(Glyph::LINE_HEAVY) . '</accent>');
        $progress->setProgressCharacter('<accent>' . Glyph::get(Glyph::LINE_HEAVY) . '</accent>');
        $progress->setEmptyBarCharacter('<border>' . Glyph::get(Glyph::LINE) . '</border>');
        $progress->setFormat(self::format($max > 0, $label !== ''));
        $progress->setMessage($label);
        $progress->minSecondsBetweenRedraws(self::REDRAW_INTERVAL_SECONDS);

        return $progress;
    }

    /**
     * Without a known total there is no bar, no percentage and no estimate to show - only how far
     * we have got and how long it has taken.
     */
    private static function format(bool $hasMax, bool $hasLabel): string
    {
        $bar = $hasMax
            ? ' %bar% <emphasis>%percent:3s%%</emphasis> <muted>%current%/%max%</muted>' .
              ' <muted>%elapsed:6s% / %estimated:-6s%</muted>'
            : ' <muted>%current%</muted> <muted>%elapsed:6s%</muted>';

        return $hasLabel ? $bar . '  <term>%message%</term>' : $bar;
    }

    /**
     * A NO_COLOR user asked for plain output, so the bar goes away rather than being drawn
     * uncoloured with box-drawing characters.
     */
    private static function isStyled(OutputInterface $output): bool
    {
        return $output->isDecorated() && !Theme::colorDisabledByEnvironment();
    }
}
