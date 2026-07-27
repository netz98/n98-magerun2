<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console;

use function Laravel\Prompts\spin;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * The shared output vocabulary for magerun commands.
 *
 * Every method has two renderings:
 *
 *  - decorated (interactive terminal): the dense visual language - uppercase headings with a
 *    row count, thin rules, glyph-prefixed status lines, de-emphasised secondary text.
 *  - undecorated (pipe, redirect, cron, CI, `--no-ansi`): the exact text previous releases
 *    produced, with no glyphs, rules or extra blank lines. Downstream scripts that grep magerun
 *    output must not break because the interactive UI was redesigned.
 *
 * `heading()` in particular reproduces AbstractMagentoCommand::writeSection()'s block byte for
 * byte when undecorated, so migrating a command from one to the other is invisible to scripts.
 */
class MagerunStyle extends SymfonyStyle
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        // Idempotent, and cheap. Guarantees the semantic tags this class writes are known to the
        // formatter even when the output was not built by Application::run(): Symfony passes
        // unregistered tags through verbatim, so a missed registration leaks markup to the user.
        Theme::apply($output);

        $this->output = $output;
        $this->formatterHelper = new FormatterHelper();
    }

    /**
     * The primary heading of a command's output, optionally with the number of items below it.
     *
     *     STORES (3)
     */
    public function heading(string $text, ?int $count = null): void
    {
        if (!$this->isStyled()) {
            // Identical to the historic writeSection() block so piped output is unchanged.
            $this->output->writeln([
                '',
                $this->formatterHelper->formatBlock($text, 'bg=blue;fg=white', true),
                '',
            ]);

            return;
        }

        $this->output->writeln('');
        $this->output->writeln(Theme::headingLine($text, $count));
    }

    /**
     * A lighter heading for a subdivision of a command's output.
     */
    public function subheading(string $text): void
    {
        if (!$this->isStyled()) {
            $this->output->writeln(['', $text, '']);

            return;
        }

        $this->output->writeln('');
        $this->output->writeln(sprintf('<subheading>%s</subheading>', $text));
    }

    /**
     * A horizontal rule, optionally labelled.
     */
    public function rule(?string $label = null): void
    {
        if (!$this->isStyled()) {
            if ($label !== null) {
                $this->output->writeln($label);
            }

            return;
        }

        $width = Theme::width();

        if ($label === null) {
            $this->output->writeln(sprintf('<border>%s</border>', Glyph::repeat(Glyph::LINE, $width)));

            return;
        }

        $visible = self::visibleWidth($label) + 2;
        $this->output->writeln(sprintf(
            '<border>%s</border> %s <border>%s</border>',
            Glyph::repeat(Glyph::LINE, 3),
            $label,
            Glyph::repeat(Glyph::LINE, max(0, $width - $visible - 3))
        ));
    }

    /**
     * An aligned key/value block - the shape most `sys:*` and `*:info` commands need.
     *
     * @param array<string, mixed> $pairs
     */
    public function keyValue(array $pairs): void
    {
        if ($pairs === []) {
            return;
        }

        if (!$this->isStyled()) {
            foreach ($pairs as $key => $value) {
                $this->output->writeln(sprintf('%s: %s', $key, self::stringify($value)));
            }

            return;
        }

        $width = 0;
        foreach (array_keys($pairs) as $key) {
            $width = max($width, self::visibleWidth((string) $key));
        }

        foreach ($pairs as $key => $value) {
            $this->output->writeln(sprintf(
                '  <term>%s</term>  %s',
                str_pad((string) $key, $width, ' ', STR_PAD_RIGHT),
                self::stringify($value)
            ));
        }
    }

    /**
     * A successful outcome.
     */
    public function ok(string $message): void
    {
        $this->status(Glyph::OK, 'success', $message);
    }

    /**
     * A failed outcome. Written to stderr so `magerun ... > file` keeps errors on the terminal.
     */
    public function fail(string $message): void
    {
        $this->status(Glyph::ERROR, 'failure', $message, true);
    }

    /**
     * Something the user should know about but which did not stop the command.
     */
    public function warn(string $message): void
    {
        $this->status(Glyph::WARNING, 'warning', $message, true);
    }

    /**
     * A neutral progress or outcome line.
     */
    public function item(string $message): void
    {
        $this->status(Glyph::INFO, 'accent', $message);
    }

    /**
     * Work that was deliberately not done.
     */
    public function skipped(string $message): void
    {
        $this->status(Glyph::PENDING, 'skipped', $message);
    }

    /**
     * Secondary guidance - what to run next, why something was chosen.
     */
    public function hint(string $message): void
    {
        if (!$this->isStyled()) {
            $this->output->writeln($message);

            return;
        }

        $this->output->writeln(sprintf('  <hint>%s</hint>', $message));
    }

    /**
     * Detail that only matters when the user asked for more verbosity.
     */
    public function detail(string $message): void
    {
        if (!$this->output->isVerbose()) {
            return;
        }

        $this->hint($message);
    }

    /**
     * Run a callback behind a spinner, returning whatever the callback returns.
     *
     * Falls back to simply announcing the work and running it when there is no terminal to
     * animate, so cron and CI logs stay clean.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function spin(callable $callback, string $label = '')
    {
        if (!$this->isStyled()) {
            if ($label !== '') {
                $this->output->writeln($label);
            }

            return $callback();
        }

        return spin($callback, $label);
    }

    /**
     * A progress bar in the dense visual language.
     *
     * @see ProgressFactory for why this is a Symfony ProgressBar and not a laravel/prompts one
     */
    public function createProgress(int $max = 0, string $label = ''): ProgressBar
    {
        return ProgressFactory::create($this->output, $max, $label);
    }

    /**
     * Iterate a collection while showing progress.
     *
     * @template TKey
     * @template TValue
     * @param iterable<TKey, TValue> $items
     * @return iterable<TKey, TValue>
     */
    public function trackProgress(iterable $items, string $label = '', ?int $max = null): iterable
    {
        if ($max === null && is_countable($items)) {
            $max = count($items);
        }

        $progress = $this->createProgress($max ?? 0, $label);
        $progress->start();

        try {
            foreach ($items as $key => $value) {
                yield $key => $value;

                $progress->advance();
            }
        } finally {
            $progress->finish();
            $this->output->writeln('');
        }
    }

    /**
     * Announce a unit of work, run it, and report the outcome on a single line.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws Throwable rethrown after the failure is reported
     */
    public function task(string $label, callable $callback)
    {
        try {
            $result = $this->spin($callback, $label);
        } catch (Throwable $exception) {
            $this->fail(sprintf('%s: %s', $label, $exception->getMessage()));

            throw $exception;
        }

        $this->ok($label);

        return $result;
    }

    /**
     * The raw output this style writes to, for the rare case a command needs it directly.
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Whether to draw the modern visual language.
     *
     * Colour alone is not enough: a `NO_COLOR` user has asked for plain output, so the glyphs and
     * rules go away too rather than being emitted uncoloured.
     */
    private function isStyled(): bool
    {
        return $this->output->isDecorated() && !Theme::colorDisabledByEnvironment();
    }

    private function status(string $glyph, string $style, string $message, bool $toErrorStream = false): void
    {
        $target = $toErrorStream ? $this->getErrorStyleOutput() : $this->output;

        if (!$this->isStyled()) {
            $target->writeln($message);

            return;
        }

        $target->writeln(sprintf('  <%s>%s</%s> %s', $style, Glyph::get($glyph), $style, $message));
    }

    private function getErrorStyleOutput(): OutputInterface
    {
        if ($this->output instanceof ConsoleOutputInterface) {
            return $this->output->getErrorOutput();
        }

        return $this->output;
    }

    /**
     * @param mixed $value
     */
    private static function stringify($value): string
    {
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            return implode(', ', array_map([self::class, 'stringify'], $value));
        }

        return (string) $value;
    }

    private static function visibleWidth(string $value): int
    {
        return Helper::width(Helper::removeDecoration(new OutputFormatter(), $value));
    }
}
