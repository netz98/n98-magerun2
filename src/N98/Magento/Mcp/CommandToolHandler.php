<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Mcp;

use Mcp\Exception\ToolCallException;
use N98\Magento\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CommandToolHandler
{
    /**
     * @var Application
     */
    private Application $application;

    /**
     * @var string
     */
    private string $commandName;

    public function __construct(Application $application, string $commandName)
    {
        $this->application = $application;
        $this->commandName = $commandName;
    }

    public function __invoke(string $arguments = ''): string
    {
        try {
            $command = $this->application->find($this->commandName);
        } catch (CommandNotFoundException $exception) {
            throw new ToolCallException($exception->getMessage(), previous: $exception);
        }

        $input = $this->buildInput($command, $arguments);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, false);

        try {
            $exitCode = $command->run($input, $output);
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
            if ($message === '') {
                $message = 'Command execution failed without an error message.';
            }
            throw new ToolCallException($message, previous: $exception);
        }

        $content = trim($output->fetch());

        if ($exitCode !== 0) {
            if ($content === '') {
                $content = 'Command produced no output.';
            }

            throw new ToolCallException(sprintf(
                "Command \"%s\" failed with exit code %d.\n\n%s",
                $this->commandName,
                $exitCode,
                $content
            ));
        }

        if ($content === '') {
            $content = sprintf('Command "%s" completed with exit code %d.', $this->commandName, $exitCode);
        }

        return $content;
    }

    /**
     * Builds the command Input directly from an argument/option map instead of relying on
     * StringInput's shell-style tokenizer. This guarantees that a free-form, multi-word value
     * (e.g. a SQL query passed to db:query) reaches the command's single scalar argument
     * completely verbatim, quotes and all, instead of requiring the MCP caller to shell-quote
     * it correctly (which is not a reliable contract for an LLM client, and still can't
     * survive quote characters embedded in the value itself).
     */
    private function buildInput(Command $command, string $arguments): ArrayInput
    {
        $definition = $command->getDefinition();

        [$parameters, $remainder] = $this->consumeOptions($definition, $arguments);

        $parameters['--no-interaction'] = true;

        $argumentNames = array_keys($definition->getArguments());
        $remainder = trim($remainder);

        if ($remainder !== '') {
            if ($argumentNames === []) {
                throw new ToolCallException(sprintf(
                    'Command "%s" does not accept any arguments, got "%s".',
                    $this->commandName,
                    $remainder
                ));
            }

            $argumentName = $argumentNames[0];
            $argument = $definition->getArgument($argumentName);

            $parameters[$argumentName] = $argument->isArray()
                ? $this->tokenizeWords($remainder)
                : $remainder;
        }

        $input = new ArrayInput($parameters);
        $input->setInteractive(false);

        return $input;
    }

    /**
     * Scans the front of the raw argument string for recognized `--option`, `--option=value`,
     * `-x` and `-x value` tokens (including the globally available `--no-interaction`/`-n`
     * flag, which lives on the Application definition rather than the command's own
     * definition). Returns the collected ArrayInput parameters plus the untouched remainder
     * of the original string (everything after the last recognized option).
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function consumeOptions(InputDefinition $definition, string $arguments): array
    {
        $parameters = [];
        $offset = 0;
        $length = strlen($arguments);

        while ($offset < $length) {
            $skipped = strspn($arguments, " \t\n\r\0\x0B", $offset);
            $offset += $skipped;

            if ($offset >= $length) {
                break;
            }

            $rest = substr($arguments, $offset);

            if (!preg_match('/^(--[A-Za-z0-9][A-Za-z0-9\-]*|-[A-Za-z])/', $rest, $match)) {
                break;
            }

            $name = $match[1];
            $consumed = strlen($name);

            $isLongOption = str_starts_with($name, '--');
            $optionName = $isLongOption ? substr($name, 2) : substr($name, 1);

            if ($optionName === 'no-interaction' || $optionName === 'n') {
                $offset += $consumed;
                continue;
            }

            $isKnownOption = $isLongOption
                ? $definition->hasOption($optionName)
                : $definition->hasShortcut($optionName);

            if (!$isKnownOption) {
                // Unknown option-looking token: stop parsing options, treat the rest as the argument value.
                break;
            }

            $option = $isLongOption ? $definition->getOption($optionName) : $definition->getOptionForShortcut($optionName);

            $value = null;
            if (substr($rest, $consumed, 1) === '=') {
                $consumed++;
                [$value, $valueLength] = $this->readWord(substr($rest, $consumed));
                $consumed += $valueLength;
            } elseif ($option->acceptValue()) {
                $afterName = substr($rest, $consumed);
                $skippedBeforeValue = strspn($afterName, " \t\n\r\0\x0B");
                [$value, $valueLength] = $this->readWord(substr($afterName, $skippedBeforeValue));
                $consumed += $skippedBeforeValue + $valueLength;
            }

            $parameters['--' . $option->getName()] = $value ?? true;
            $offset += $consumed;
        }

        return [$parameters, substr($arguments, $offset)];
    }

    /**
     * Reads a single word from the start of $str: either a quoted string (single or double
     * quotes, matching the opposite quote type not requiring escaping) or an unquoted run of
     * non-whitespace characters.
     *
     * @return array{0: string, 1: int} the word value and the number of characters consumed
     */
    private function readWord(string $str): array
    {
        if ($str === '') {
            return ['', 0];
        }

        $quote = $str[0];
        if ($quote === '"' || $quote === "'") {
            $end = strpos($str, $quote, 1);
            if ($end !== false) {
                return [substr($str, 1, $end - 1), $end + 1];
            }
        }

        $unquotedLength = strcspn($str, " \t\n\r\0\x0B");

        return [substr($str, 0, $unquotedLength), $unquotedLength];
    }

    /**
     * Splits a string into words for IS_ARRAY arguments, respecting simple quoting.
     *
     * @return string[]
     */
    private function tokenizeWords(string $str): array
    {
        $words = [];
        $offset = 0;
        $length = strlen($str);

        while ($offset < $length) {
            $skipped = strspn($str, " \t\n\r\0\x0B", $offset);
            $offset += $skipped;

            if ($offset >= $length) {
                break;
            }

            [$word, $consumed] = $this->readWord(substr($str, $offset));
            if ($consumed === 0) {
                break;
            }

            $words[] = $word;
            $offset += $consumed;
        }

        return $words;
    }
}
