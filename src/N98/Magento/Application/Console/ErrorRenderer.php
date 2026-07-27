<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console;

use N98\Util\Console\Glyph;
use N98\Util\Console\Theme;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException as ConsoleRuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Renders an uncaught throwable as a compact error report.
 *
 * Symfony's default renderer draws a full-width red block, which pushes the actual message off to
 * the side and buries the one thing a user needs: what to do next. This renderer leads with the
 * message, adds a hint for the failure modes magerun sees most often, and only shows the stack
 * trace when the user asked for verbose output.
 */
final class ErrorRenderer
{
    /**
     * How many alternatives to offer for a mistyped command.
     */
    private const MAX_ALTERNATIVES = 5;

    /**
     * Hints keyed by a pattern matched against the exception message.
     *
     * @var array<string, string>
     */
    private const HINTS = [
        '/magento folder could not be detected/i' => 'Run magerun from inside a Magento directory, or point it at one with --root-dir=/path/to/magento',
        '/must be run from a magento/i' => 'Run magerun from inside a Magento directory, or point it at one with --root-dir=/path/to/magento',
        '/(app\/etc\/env\.php|env\.php).*(not|missing)/i' => 'This Magento installation has no app/etc/env.php yet - it looks like setup:install has not run.',
        '/(access denied for user|connection refused|sqlstate\[hy000\] \[10\d\d\])/i' => 'Check the database credentials in app/etc/env.php, and that the database server is reachable.',
        '/unknown database/i' => 'The database named in app/etc/env.php does not exist. Create it, or fix the db name.',
        '/area code (is )?not set/i' => 'This command needs a Magento area. Report it if a core magerun command triggers this.',
        '/is not available because/i' => 'This command is gated behind a capability check - the message above says which one.',
        '/permission denied/i' => 'Check file ownership and permissions; magerun needs the same access as your web server user.',
        '/allowed memory size|memory_limit/i' => 'Raise PHP\'s memory limit, e.g. php -d memory_limit=2G $(which n98-magerun2) ...',
    ];

    /**
     * @param array<int, string> $allCommandNames used to suggest alternatives for typos
     */
    public function render(
        Throwable $throwable,
        OutputInterface $output,
        ?string $commandName = null,
        array $allCommandNames = []
    ): void {
        $width = Theme::width();
        $decorated = $output->isDecorated() && !Theme::colorDisabledByEnvironment();

        $output->writeln('');

        if ($decorated) {
            $output->writeln('<failure>ERROR</failure>');
            $output->writeln(sprintf('<border>%s</border>', Glyph::repeat(Glyph::LINE, $width)));
        } else {
            // Keep the historic prefix so scripts grepping for it still match.
            $output->writeln(sprintf('[%s]', $this->shortClassName($throwable)));
        }

        foreach ($this->messageLines($throwable, $decorated) as $line) {
            $output->writeln($decorated ? '  ' . rtrim($line) : $line);
        }

        if ($decorated) {
            $this->renderAlternatives($throwable, $output, $allCommandNames);
        }
        $this->renderFacts($throwable, $output, $commandName, $decorated);
        $this->renderHint($throwable, $output, $decorated);
        $this->renderTrace($throwable, $output, $decorated);

        $output->writeln('');
    }

    /**
     * @return array<int, string>
     */
    private function messageLines(Throwable $throwable, bool $decorated): array
    {
        $message = trim($throwable->getMessage());

        if ($message === '') {
            $message = sprintf('%s was thrown with no message.', $this->shortClassName($throwable));
        }

        // Symfony already appends "Did you mean one of these?" and the list to this exception's
        // message. Undecorated output keeps it verbatim; decorated output drops it in favour of the
        // styled block below, which would otherwise repeat the same names twice.
        if ($decorated && $throwable instanceof CommandNotFoundException) {
            $message = trim(explode("\n", $message)[0]);
        }

        return explode("\n", $message);
    }

    /**
     * @param array<int, string> $allCommandNames
     */
    private function renderAlternatives(
        Throwable $throwable,
        OutputInterface $output,
        array $allCommandNames
    ): void {
        $alternatives = $this->findAlternatives($throwable, $allCommandNames);

        if ($alternatives === []) {
            return;
        }

        $output->writeln('');
        $output->writeln('  <term>Did you mean</term>');

        foreach ($alternatives as $alternative) {
            $output->writeln(sprintf('    <accent>%s</accent>', $alternative));
        }
    }

    /**
     * Alternatives come from Symfony when it recognises a near-miss; otherwise fall back to our
     * own similarity search so a mistyped namespace still gets suggestions.
     *
     * @param array<int, string> $allCommandNames
     * @return array<int, string>
     */
    private function findAlternatives(Throwable $throwable, array $allCommandNames): array
    {
        if (!$throwable instanceof CommandNotFoundException) {
            return [];
        }

        $alternatives = $throwable->getAlternatives();

        if ($alternatives === [] && $allCommandNames !== []) {
            $alternatives = $this->guessCommandNames($throwable->getMessage(), $allCommandNames);
        }

        return array_slice(array_values($alternatives), 0, self::MAX_ALTERNATIVES);
    }

    /**
     * @param array<int, string> $allCommandNames
     * @return array<int, string>
     */
    private function guessCommandNames(string $message, array $allCommandNames): array
    {
        if (preg_match('/"([^"]+)"/', $message, $matches) !== 1) {
            return [];
        }

        $needle = $matches[1];
        $scored = [];

        foreach ($allCommandNames as $name) {
            similar_text($needle, $name, $percent);

            if ($percent >= 55.0 || str_contains($name, $needle)) {
                $scored[$name] = $percent;
            }
        }

        arsort($scored);

        return array_keys($scored);
    }

    private function renderFacts(
        Throwable $throwable,
        OutputInterface $output,
        ?string $commandName,
        bool $decorated
    ): void {
        if (!$decorated) {
            return;
        }

        $facts = [];

        if ($commandName !== null && $commandName !== '') {
            $facts['command'] = $commandName;
        }

        // A console RuntimeException is a usage error (bad option, missing argument); the class
        // name adds nothing there. For anything else it tells the user what actually broke.
        if (!$throwable instanceof ConsoleRuntimeException && !$throwable instanceof CommandNotFoundException) {
            $facts['exception'] = $this->shortClassName($throwable);
        }

        if ($output->isVerbose()) {
            $facts['at'] = sprintf('%s:%d', $throwable->getFile(), $throwable->getLine());
        }

        if ($facts === []) {
            return;
        }

        $output->writeln('');

        $labelWidth = max(array_map('strlen', array_keys($facts)));
        foreach ($facts as $label => $value) {
            $output->writeln(sprintf(
                '  <term>%s</term>  %s',
                str_pad($label, $labelWidth, ' ', STR_PAD_RIGHT),
                $value
            ));
        }
    }

    private function renderHint(Throwable $throwable, OutputInterface $output, bool $decorated): void
    {
        $hint = $this->findHint($throwable);

        if ($hint === null) {
            return;
        }

        $output->writeln('');
        $output->writeln($decorated ? sprintf('  <hint>%s</hint>', $hint) : $hint);
    }

    private function findHint(Throwable $throwable): ?string
    {
        $message = $throwable->getMessage();

        foreach (self::HINTS as $pattern => $hint) {
            if (preg_match($pattern, $message) === 1) {
                return $hint;
            }
        }

        return null;
    }

    private function renderTrace(Throwable $throwable, OutputInterface $output, bool $decorated): void
    {
        if (!$output->isVerbose()) {
            if ($decorated) {
                $output->writeln('');
                $output->writeln('  <hint>Re-run with -v for the stack trace.</hint>');
            }

            return;
        }

        $current = $throwable;
        while ($current !== null) {
            $output->writeln('');
            $output->writeln($decorated
                ? sprintf('<border>%s</border>', Glyph::repeat(Glyph::LINE, Theme::width()))
                : '');
            $output->writeln(sprintf(
                '%s: %s',
                $this->shortClassName($current),
                trim($current->getMessage())
            ));
            $output->writeln($current->getTraceAsString());

            $current = $current->getPrevious();
        }
    }

    private function shortClassName(Throwable $throwable): string
    {
        $parts = explode('\\', get_class($throwable));

        return (string) end($parts);
    }
}
