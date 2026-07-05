<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Console\Prompts;

use InvalidArgumentException;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\TextPrompt;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Wires laravel/prompts select()/confirm()/suggest()/text()/password() to fall back to the
 * equivalent SymfonyStyle prompt on platforms/environments that can't do raw-terminal input.
 */
trait ConfiguresPromptFallbacks
{
    protected function configurePrompts(SymfonyStyle $io): void
    {
        Prompt::fallbackWhen($this->shouldFallbackToPlainPrompts());

        SelectPrompt::fallbackUsing(static fn (SelectPrompt $prompt): int|string => $io->choice(
            $prompt->label,
            $prompt->options,
            $prompt->default
        ));

        ConfirmPrompt::fallbackUsing(static fn (ConfirmPrompt $prompt): bool => $io->confirm(
            $prompt->label,
            $prompt->default
        ));

        SuggestPrompt::fallbackUsing(static fn (SuggestPrompt $prompt): string => (string) $io->ask(
            $prompt->label,
            $prompt->default,
            self::promptValueValidator($prompt)
        ));

        TextPrompt::fallbackUsing(static fn (TextPrompt $prompt): string => (string) $io->ask(
            $prompt->label,
            $prompt->default ?: null,
            self::promptValueValidator($prompt)
        ));

        PasswordPrompt::fallbackUsing(static fn (PasswordPrompt $prompt): string => (string) $io->askHidden(
            $prompt->label,
            self::promptValueValidator($prompt)
        ));
    }

    /**
     * Bridges a Laravel Prompts `validate` callback (returns an error string, or null when
     * valid) into a Symfony Question validator (returns the value, or throws when invalid) so
     * validation still runs on the SymfonyStyle fallback path.
     */
    private static function promptValueValidator(Prompt $prompt): ?callable
    {
        if (!is_callable($prompt->validate)) {
            return null;
        }

        return static function ($value) use ($prompt) {
            $error = ($prompt->validate)($value);
            if (is_string($error) && $error !== '') {
                throw new InvalidArgumentException($error);
            }

            return $value;
        };
    }

    /**
     * laravel/prompts doesn't support Windows and reads directly from STDIN, bypassing
     * Symfony's input stream - so it can't be driven by CommandTester either.
     */
    protected function shouldFallbackToPlainPrompts(): bool
    {
        return OperatingSystem::isWindows() || !stream_isatty(STDIN);
    }
}
