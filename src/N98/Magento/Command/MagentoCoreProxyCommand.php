<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

        $processCommand = [
            $this->magentoRootDir . '/bin/magento',
            $this->getRawInput(),
        ];

        $process = new Process($processCommand, $this->magentoRootDir);
        $process->setTimeout($config['timeout']);
        $process->setTty(true);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return $process->getExitCode();
    }

    private function getRawInput(): string
    {
        // remove first array key => n98-magerun2.phar path -> we replace them with bin/magento later on.
        return implode(' ', array_slice($_SERVER['argv'], 1));
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
            $normalizedShortcut = substr($option['name'], 1);

            if (in_array(
                $normalizedName,
                ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction'])
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

            $inputDefinition->addOption(
                new InputOption(
                    $normalizedName,
                    $normalizedShortcut,
                    $mode,
                    $option['description'],
                    $mode === InputArgument::OPTIONAL ? $option['default'] : null
                )
            );
        }
    }
}
