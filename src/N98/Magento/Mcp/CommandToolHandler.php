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
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\StringInput;
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

        $argumentString = trim($arguments);
        if ($argumentString === '') {
            $argumentString = '--no-interaction';
        } elseif (!preg_match('/(^|\\s)(--no-interaction|-n)(\\s|$)/', $argumentString)) {
            $argumentString = '--no-interaction ' . $argumentString;
        }

        $input = new StringInput($argumentString);
        $input->setInteractive(false);

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
}
