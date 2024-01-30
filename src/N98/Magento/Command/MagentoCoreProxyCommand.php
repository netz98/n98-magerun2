<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command;

use N98\Magento\Application\Console\Input\FilteredStringInput;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MagentoCoreProxyCommand extends AbstractMagentoCommand
{
    /**
     * @var string
     */
    private $magentoRootDir;

    public function __construct(
        string $magentoRootDir,
        string $commandName,
        array  $usage,
        string $description,
        string $help,
        array  $definition
    ) {
        parent::__construct($commandName);

        foreach ($usage as $u) {
            $this->addUsage($u);
        }

        $this->setDescription($description);
        $this->setHelp($help);

        $this->processInputDefinition($definition);
        $this->magentoRootDir = $magentoRootDir;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getCommandConfig();

        $magentoCoreCommandInput = new FilteredStringInput(escapeshellcmd($input->__toString()));
        $envVariablesForBinMagento = $_ENV;

        if ($config['is_env_variables_filtering_enabled'] === true) {
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<debug>Filtering environment variables for bin/magento</debug>');
                // print filtered environment variables
                foreach ($config['env_variables_to_filter'] as $key) {
                    $output->writeln(sprintf('<debug>  - <comment>%s</comment></debug>', $key));
                }
            }
            $envVariablesForBinMagento = $this->filterEnvironmentVariables(
                $config['env_variables_to_filter']
            );
        }

        $process = Process::fromShellCommandline(
            OperatingSystem::getPhpBinary() . ' ' . $this->magentoRootDir . '/bin/magento ' . $magentoCoreCommandInput->__toString(),
            $this->magentoRootDir,
            $envVariablesForBinMagento
        );

        $process->setTimeout($config['timeout']);
        $process->setTty($input->isInteractive());

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln(sprintf('<debug>Execute: <comment>%s</comment></debug>', $process->getCommandLine()));
            $output->writeln(sprintf('<debug>  - Timeout: <comment>%d</comment></debug>', $process->getTimeout()));
            $output->writeln(sprintf('<debug>  - TTY: <comment>%b</comment></debug>', $process->isTty()));
        }

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $process->run(function ($type, $buffer) use ($output, $errOutput) {
            if (Process::ERR === $type) {
                $errOutput->write($buffer);
            } else {
                $output->write($buffer);
            }
        });

        return $process->getExitCode();
    }

    /**
     * @param array $definition
     * @return void
     */
    private function processInputDefinition($definition): void
    {
        $inputDefinition = new InputDefinition();

        $this->processInputDefinitionArguments($inputDefinition, $definition['arguments']);
        $this->processInputDefinitionOptions($inputDefinition, $definition['options']);

        $this->setDefinition($inputDefinition);
    }

    /**
     * @param InputDefinition $inputDefinition
     * @param array $arguments
     * @return void
     */
    private function processInputDefinitionArguments(InputDefinition $inputDefinition, array $arguments): void
    {
        foreach ($arguments as $argument) {
            $mode = InputArgument::OPTIONAL;
            if ($argument['is_required']) {
                $mode = InputArgument::REQUIRED;
            }
            if ($argument['is_array']) {
                $mode |= InputArgument::IS_ARRAY;
            }

            $inputDefinition->addArgument(
                new InputArgument(
                    $argument['name'],
                    $mode,
                    $argument['description'],
                    $mode === InputArgument::OPTIONAL ? $argument['default'] : null
                )
            );
        }
    }

    /**
     * @param InputDefinition $inputDefinition
     * @param array $options
     * @return void
     */
    private function processInputDefinitionOptions(InputDefinition $inputDefinition, array $options): void
    {
        foreach ($options as $option) {
            // remove "--" at start
            $normalizedName = substr($option['name'], 2);
            $normalizedShortcut = substr($option['shortcut'], 1);

            if (
                in_array(
                    $normalizedName,
                    ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction']
                )
            ) {
                continue;
            }

            $mode = InputOption::VALUE_NONE;

            if ($option['accept_value']) {
                $mode = InputOption::VALUE_OPTIONAL;
            }

            if ($option['is_value_required']) {
                $mode |= InputOption::VALUE_REQUIRED;
            }

            if ($option['is_multiple']) {
                $mode |= InputOption::VALUE_IS_ARRAY;
            }

            $defaultValue = true;
            if ($option['accept_value']) {
                $defaultValue = $option['default'];
            }

            $inputDefinition->addOption(
                new InputOption(
                    $normalizedName,
                    $normalizedShortcut,
                    $mode,
                    $option['description'],
                    $mode !== InputOption::VALUE_NONE ? $defaultValue : null
                )
            );
        }
    }

    /**
     * @param array $filterList
     * @return array
     */
    protected function filterEnvironmentVariables(array $filterList): array
    {
        $envForBinMagento = $_ENV;

        foreach ($filterList as $key) {
            unset($envForBinMagento[$key]);
        }

        return $envForBinMagento;
    }
}
