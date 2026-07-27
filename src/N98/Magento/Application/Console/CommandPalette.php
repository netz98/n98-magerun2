<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console;

use Laravel\Prompts\Prompt;
use function Laravel\Prompts\search;
use N98\Util\Console\Interaction;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Fuzzy command finder shown when magerun is started without a command.
 *
 * Dumping 200+ commands at someone who typed `n98-magerun2` and pressed enter is not an answer to
 * the question they were asking. On an interactive terminal they get a search box instead; every
 * non-interactive caller still gets the full `list` output, unchanged.
 */
final class CommandPalette
{
    /**
     * Rows of the result list to keep on screen.
     */
    private const SCROLL = 12;

    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Whether the palette should replace the default `list` behaviour for this invocation.
     */
    public function isApplicable(InputInterface $input, OutputInterface $output): bool
    {
        // Only when no command at all was given - `magerun --root-dir=/x` still lands here.
        if ($input->getFirstArgument() !== null) {
            return false;
        }

        // `--help` and `--version` are handled by Symfony before a command is resolved.
        if ($input->hasParameterOption(['--help', '-h'], true)
            || $input->hasParameterOption(['--version', '-V'], true)) {
            return false;
        }

        return Interaction::isPromptable($input, $output);
    }

    /**
     * Ask the user to pick a command.
     *
     * @return string|null the chosen command name, or null if the user aborted
     */
    public function choose(OutputInterface $output): ?string
    {
        $commands = $this->selectableCommands();

        if ($commands === []) {
            return null;
        }

        $output->writeln($this->application->getHelp());

        try {
            $chosen = search(
                label: 'Which command do you want to run?',
                options: fn (string $value): array => $this->match($commands, $value),
                placeholder: 'Start typing, e.g. cache, db:dump, sys:info',
                scroll: self::SCROLL,
                hint: sprintf('%d commands available. Ctrl+C to quit.', count($commands))
            );
        } catch (Throwable $e) {
            // Ctrl+C and a terminal that turns out not to support raw input both land here; either
            // way, quietly fall back to doing nothing rather than dumping a stack trace.
            return null;
        }

        return is_string($chosen) && $chosen !== '' ? $chosen : null;
    }

    /**
     * Restore the terminal after the palette so the chosen command starts from a clean state.
     */
    public static function restoreTerminal(): void
    {
        try {
            Prompt::terminal()->restoreTty();
        } catch (Throwable $e) {
            // Nothing to restore, e.g. when no prompt ever grabbed the terminal.
        }
    }

    /**
     * Insert the chosen command into the original argv so global options the user already typed
     * are preserved.
     *
     * @param array<int, string> $argv
     */
    public function buildInput(array $argv, string $commandName): ArgvInput
    {
        array_splice($argv, 1, 0, [$commandName]);

        return new ArgvInput($argv, $this->application->getDefinition());
    }

    /**
     * @return array<string, string> command name => description
     */
    private function selectableCommands(): array
    {
        $commands = [];

        foreach ($this->application->all() as $name => $command) {
            if ($this->isHidden($command, $name)) {
                continue;
            }

            $commands[$name] = $command->getDescription();
        }

        ksort($commands);

        return $commands;
    }

    private function isHidden(Command $command, string $name): bool
    {
        // `all()` is keyed by name *and* alias; keep only the canonical entry.
        if ($command->getName() !== $name) {
            return true;
        }

        if ($command->isHidden() || !$command->isEnabled()) {
            return true;
        }

        // Symfony's completion plumbing is not something a human picks from a menu.
        return in_array($name, ['_complete', 'completion', 'help', 'list'], true);
    }

    /**
     * Rank commands against what the user has typed.
     *
     * A name match always outranks a description match, so typing "dump" offers `db:dump` before
     * the commands that merely mention dumping.
     *
     * @param array<string, string> $commands
     * @return array<string, string> command name => label shown in the list
     */
    private function match(array $commands, string $value): array
    {
        $needle = trim(mb_strtolower($value));

        $nameMatches = [];
        $descriptionMatches = [];

        foreach ($commands as $name => $description) {
            $label = $this->label($name, $description);

            if ($needle === '') {
                $nameMatches[$name] = $label;
                continue;
            }

            if (str_contains(mb_strtolower($name), $needle)) {
                $nameMatches[$name] = $label;
                continue;
            }

            if (str_contains(mb_strtolower($description), $needle)) {
                $descriptionMatches[$name] = $label;
            }
        }

        return $nameMatches + $descriptionMatches;
    }

    private function label(string $name, string $description): string
    {
        if ($description === '') {
            return $name;
        }

        return sprintf('%s  —  %s', $name, $description);
    }
}
