<?php

namespace N98\Magento\Command\Developer\Console;

use Exception;
use N98\Magento\Command\Developer\Console\Exception\NoModuleDefinedException;
use N98\Util\BinaryString;
use Psy\Exception\BreakException;
use Psy\Exception\ErrorException;
use Psy\Exception\FatalErrorException;
use Psy\Exception\ParseErrorException;
use Psy\Shell as PsyShell;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Shell
 * @package N98\Magento\Command\Developer\Console
 */
class Shell extends PsyShell
{
    /**
     * @var string
     */
    private $prompt = '';

    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->registerHelpersFromMainApplication();

        return parent::run($input, $output);
    }

    private function registerHelpersFromMainApplication()
    {
        $helperSetMagerun = $this->getScopeVariable('magerun')->getHelperSet();
        $helperSetPsy = $this->getHelperSet();

        foreach ($helperSetMagerun as $helper) {
            /** @var $helper HelperInterface */
            if (!$helperSetPsy->has($helper->getName())) {
                $helperSetPsy->set($helper);
            }
        }
    }

    /**
     * Renders a caught Exception.
     *
     * Exceptions are formatted according to severity. ErrorExceptions which were
     * warnings or Strict errors aren't rendered as harshly as real errors.
     *
     * Stores $e as the last Exception in the Shell Context.
     *
     * @param Exception $e An exception instance
     * @throws ErrorException
     * @throws FatalErrorException
     */
    public function writeException(Exception $e)
    {
        $this->resetCodeBuffer();

        if ($e instanceof BreakException) {
            return;
        }

        if ($e instanceof NoModuleDefinedException) {
            $this->getConsoleOutput()->writeln('<warning>' . $e->getMessage() . '</warning>');

            return;
        } elseif ($e instanceof ErrorException) {
            if (BinaryString::startsWith($e->getMessage(), 'PHP error:  Use of undefined constant')) {
                $this->getConsoleOutput()->writeln('<warning>Unknown command</warning>');

                return;
            }
        } elseif ($e instanceof FatalErrorException) {
            if (BinaryString::startsWith($e->getMessage(), 'PHP Fatal error:  Call to undefined function')) {
                $this->getConsoleOutput()->writeln('<warning>Unknown function</warning>');

                return;
            }
        } elseif ($e instanceof ParseErrorException) {
            $message = substr($e->getMessage(), 0, strpos($e->getMessage(), ' on line'));
            $this->getConsoleOutput()->writeln('<error>' . $message . '</error>');

            return;
        }

        parent::writeException($e);
    }

    /**
     * @return string
     */
    protected function getPrompt()
    {
        if (!empty($this->prompt)) {
            return $this->prompt;
        }

        return parent::getPrompt();
    }

    /**
     * @param string $prompt
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * Resets prompt to default
     */
    public function resetPrompt()
    {
        $this->prompt = '';
    }

    /**
     * @return ConsoleOutput
     */
    private function getConsoleOutput()
    {
        if ($this->consoleOutput === null) {
            $formatter = new OutputFormatter();
            $formatter->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
            $this->consoleOutput = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
        }

        return $this->consoleOutput;
    }
}
