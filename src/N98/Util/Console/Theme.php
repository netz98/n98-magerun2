<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\AnsiColorMode;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Central definition of every semantic output style used by magerun.
 *
 * Colours are deliberately taken from the terminal's own 16-colour palette (plus `gray`, i.e.
 * bright black, for de-emphasis) instead of fixed hex values, so output stays legible on light
 * and dark backgrounds and respects whatever theme the user has configured.
 *
 * Symfony strips every style tag when the output is not decorated, so registering styles here
 * has no effect on piped, redirected or `--no-ansi` output.
 *
 * Tag names deliberately avoid HTML element names - hence `term` and `emphasis` rather than the
 * more obvious `label` and `strong`. Symfony emits an *unregistered* tag verbatim but consumes a
 * registered one, so naming a style after an HTML element silently eats that element out of any
 * data magerun renders. Magento's system.xml labels are HTML fragments, so `config:search` would
 * otherwise lose the markup its previous releases printed.
 */
final class Theme
{
    /**
     * Fallback width when the terminal size cannot be determined (pipes, cron, CI).
     */
    public const DEFAULT_WIDTH = 80;

    /**
     * Never draw wider than this, even on a maximised 4K terminal - long rules are hard to scan.
     */
    public const MAX_WIDTH = 120;

    /**
     * @var array<string, array{0: string, 1: string, 2: array<int, string>}>
     *      tag => [foreground, background, options]
     */
    private const STYLES = [
        // Structure
        'heading' => ['cyan', '', ['bold']],
        'subheading' => ['default', '', ['bold']],
        'border' => ['gray', '', []],
        'muted' => ['gray', '', []],
        'hint' => ['gray', '', []],
        'count' => ['gray', '', []],
        'term' => ['gray', '', []],
        'accent' => ['cyan', '', []],
        'emphasis' => ['default', '', ['bold']],

        // Status
        'success' => ['green', '', []],
        'failure' => ['red', '', []],
        'skipped' => ['gray', '', []],

        // Value types, promoted from Magerun\ConfigDumpCommand so any command can highlight data
        'key' => ['cyan', '', ['bold']],
        'string' => ['green', '', []],
        'number' => ['magenta', '', []],
        'bool' => ['yellow', '', []],
        'null' => ['gray', '', []],

        // Overrides of pre-existing tags. `warning` used to be bold red-on-yellow and `debug`
        // magenta-on-white; both were close to unreadable on a dark background.
        'warning' => ['yellow', '', ['bold']],
        'debug' => ['magenta', '', []],
    ];

    /**
     * Register every semantic style on an output's formatter.
     *
     * Applies to the error stream too, so `<hint>` and friends work in messages written to stderr.
     */
    public static function apply(OutputInterface $output): void
    {
        self::applyToSingleOutput($output);

        if ($output instanceof ConsoleOutputInterface) {
            self::applyToSingleOutput($output->getErrorOutput());
        }
    }

    /**
     * Usable width for rules, headings and tables, clamped to something readable.
     */
    public static function width(): int
    {
        $width = (new Terminal())->getWidth();

        if ($width <= 0) {
            return self::DEFAULT_WIDTH;
        }

        return (int) max(40, min($width, self::MAX_WIDTH));
    }

    /**
     * The one definition of a section heading: an upper-case label with an optional item count.
     *
     * Deliberately without an underlining rule. Nearly every heading in magerun introduces a table,
     * and a table already draws its own top border directly underneath - a second rule of a
     * different width above it just looks broken.
     *
     * @param string $label
     * @param int|null $count
     */
    public static function headingLine(string $label, ?int $count = null): string
    {
        // Trimmed because some headings were padded to centre them inside the old blue block.
        $label = trim($label);

        // Upper-casing a path or a version would just shout, so those are left as they are.
        if (preg_match('/[\/\\\\@]|\d\.\d/', $label) !== 1) {
            $label = mb_strtoupper($label);
        }

        return sprintf(
            '<heading>%s</heading>%s',
            $label,
            $count === null ? '' : sprintf(' <count>(%d)</count>', $count)
        );
    }

    /**
     * True when the terminal advertises 24-bit colour support.
     *
     * Only needed for gradients; ordinary styles let Symfony degrade colours on its own.
     */
    public static function supportsTrueColor(): bool
    {
        return Terminal::getColorMode() === AnsiColorMode::Ansi24;
    }

    /**
     * True when the user has asked for colourless output via the NO_COLOR convention or a dumb
     * terminal. Callers should treat this as "also skip decorative structure", not just colour.
     *
     * @see https://no-color.org
     */
    public static function colorDisabledByEnvironment(): bool
    {
        $noColor = getenv('NO_COLOR');
        if ($noColor !== false && $noColor !== '') {
            return true;
        }

        return getenv('TERM') === 'dumb';
    }

    private static function applyToSingleOutput(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        foreach (self::STYLES as $tag => [$foreground, $background, $options]) {
            $formatter->setStyle($tag, new OutputFormatterStyle($foreground, $background, $options));
        }
    }
}
