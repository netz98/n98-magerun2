<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use Exception;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State as AppState;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to run a sequence of common development tasks.
 */
class KeepCalmCommand extends AbstractMagentoCommand
{
    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * List of commands and their metadata to be executed in sequence.
     *
     * @var array<string, array{description: string, commandString: string}>
     */
    private array $commands = [
        'hyva:config:generate' => [
            'description' => 'Generate Hyvä theme configuration files if they are missing.',
            'commandString' => 'hyva:config:generate',
        ],
        'setup:upgrade' => [
            'description' => 'Run setup upgrade and database schema/data updates. Clears also the cache.',
            'commandString' => 'setup:upgrade',
        ],
        'db:add-default-authorization-entries' => [
            'description' => 'Add default authorization entries for admin users if they are missing.',
            'commandString' => 'db:add-default-authorization-entries',
        ],
        'generation:flush' => [
            'description' => 'Flushes the generated code in generation/code directory.',
            'commandString' => 'generation:flush',
        ],
        'setup:di:compile' => [
            'description' => 'Compile dependency injection configuration and generate code.',
            'commandString' => 'setup:di:compile',
        ],
        'dev:asset:clear' => [
            'description' => 'Clear the generated static assets.',
            'commandString' => 'dev:asset:clear',
        ],
        'dev:theme:build-hyva' => [
            'description' => 'Build Hyvä theme CSS and JS files.',
            'commandString' => 'dev:theme:build-hyva --all --suppress-no-theme-found-error',
        ],
        'setup:static-content:deploy' => [
            'description' => 'Deploy static content for the current locale',
            'commandString' => 'setup:static-content:deploy',
        ],
        'indexer:reset' => [
            'description' => 'Reset all indexers to their initial state.',
            'commandString' => 'indexer:reset',
        ],
        'indexer:reindex' => [
            'description' => 'Update all indexer data. This often helps if something is not displayed correctly in the frontend.',
            'commandString' => 'indexer:reindex',
        ],
        'maintenance:disable' => [
            'description' => 'Disable maintenance mode and let you see your frontend again.',
            'commandString' => 'maintenance:disable',
        ],
    ];

    /**
     * @param DeploymentConfig $deploymentConfig
     * @return void
     */
    public function inject(DeploymentConfig $deploymentConfig): void
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('dev:keep-calm')
            ->setDescription(
                'Run cache clean, reindex, setup upgrade, di compile and static content deploy (experimental)'
            );

        // Add a --skip-<command> option for each command in the sequence.
        foreach (array_keys($this->commands) as $commandName) {
            $optionName = 'skip-' . str_replace(':', '-', $commandName);
            $this->addOption(
                $optionName,
                null,
                InputOption::VALUE_NONE,
                'Skip execution of ' . $commandName . ' command.'
            );
        }

        // This option is kept for backward compatibility and specific use cases.
        $this->addOption(
            'force-static-content-deploy',
            null,
            InputOption::VALUE_NONE,
            'Force static content deploy with --force option, even in developer/default mode.'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getApplication()->setAutoExit(false);
        $isProductionMode = $this->deploymentConfig->get(AppState::PARAM_MODE) === AppState::MODE_PRODUCTION;
        $anyFailed = false;
        $commandIndex = 1;
        $executedCommands = [];

        foreach ($this->commands as $commandName => $commandData) {
            $description = $commandData['description'];
            $commandString = $commandData['commandString'];

            // Special handling for hyva:config:generate
            if ($commandName === 'hyva:config:generate') {
                if (file_exists('app/etc/hyva-themes.json')) {
                    $executedCommands[] = [
                        'index' => $commandIndex,
                        'name' => $commandName,
                        'description' => $description,
                        'status' => 'skipped',
                        'reason' => 'app/etc/hyva-themes.json exists',
                    ];
                    $commandIndex++;
                    continue;
                }
            }
            if ($commandName === 'setup:static-content:deploy') {
                $forceDeploy = $input->getOption('force-static-content-deploy');
                if (!$isProductionMode && !$forceDeploy) {
                    $this->writeCommandBanner($commandName, $description, $output, $commandIndex);
                    $this->writeInfoMessage('Skipping static content deploy (not required in current mode).', $output);
                    $executedCommands[] = [
                        'index' => $commandIndex,
                        'name' => $commandName,
                        'description' => $description,
                        'status' => 'skipped',
                        'reason' => 'Not required in current mode',
                    ];
                    $commandIndex++;
                    continue;
                }
                if ($forceDeploy && !preg_match('/(^|\s)-f(\s|$)/', $commandString)) {
                    $commandString .= ' -f';
                }
            }

            if ($this->shouldSkipCommand($commandName, $input, $output)) {
                $executedCommands[] = [
                    'index' => $commandIndex,
                    'name' => $commandName,
                    'description' => $description,
                    'status' => 'skipped',
                    'reason' => 'Skipped by user option',
                ];
                $commandIndex++;
                continue;
            }

            $success = $this->runCommand($commandString, $description, $output, $commandIndex);
            if ($success === null) {
                $executedCommands[] = [
                    'index' => $commandIndex,
                    'name' => $commandName,
                    'description' => $description,
                    'status' => 'skipped',
                    'reason' => 'Command not found',
                ];
                continue;
            }

            $executedCommands[] = [
                'index' => $commandIndex,
                'name' => $commandName,
                'description' => $description,
                'status' => $success ? 'success' : 'failed',
                'reason' => null,
            ];
            $commandIndex++;
            if (!$success) {
                $anyFailed = true;
            }
        }

        $this->writeRandomMessageAtTheEnd($output, $anyFailed);
        $this->writeCommandSummaryChecklist($output, $executedCommands);

        return Command::SUCCESS;
    }

    /**
     * Checks if a command should be skipped based on input options.
     */
    private function shouldSkipCommand(string $commandName, InputInterface $input, OutputInterface $output): bool
    {
        $skipOptionName = 'skip-' . str_replace(':', '-', $commandName);
        if ($input->getOption($skipOptionName)) {
            $this->writeInfoMessage("Skipping command: <comment>{$commandName}</comment>", $output);

            return true;
        }

        return false;
    }

    /**
     * Executes a given sub-command string and handles output and errors.
     */
    private function runCommand(string $commandString, string $description, OutputInterface $output, int $commandIndex = 1): ?bool
    {
        try {
            // The command name is the first part of the string (e.g., 'setup:upgrade' from 'setup:upgrade --force').
            $commandName = explode(' ', $commandString, 2)[0];

            /**
             * Run the commands
             */
            try {
                $command = $this->getApplication()->find($commandName);
            } catch (CommandNotFoundException $e) {
                return null;
            }

            $this->writeCommandBanner($commandName, $description, $output, $commandIndex);
            $output->writeln('<info>What is going on?</info> -> <comment>' . $description . '</comment>');

            $commandInput = new StringInput($commandString);
            $exitCode = $command->run($commandInput, $output);

            if (Command::SUCCESS === $exitCode) {
                $output->writeln("\n✅  <info>Command {$commandName} finished successfully!</info>");
                return true;
            }

            $output->writeln("\n😥  <error>Command {$commandName} failed with exit code {$exitCode}.</error>");

        } catch (Exception $e) {
            $output->writeln("\n😥  <error>Something went wrong while running {$commandName}: {$e->getMessage()}</error>");
        }

        $output->writeln("🍵  <info>Stay calm and grab a tea or coffee! Continuing ...</info>");
        return false;
    }

    /**
     * Writes a formatted banner for a command execution.
     */
    private function writeCommandBanner(string $commandName, string $description, OutputInterface $output, int $commandIndex = 1): void
    {
        $this->writeSectionBanner(
            "{$commandIndex}. 🚀  <info>Running command:</info> <comment>{$commandName}</comment>",
            $output
        );
    }

    /**
     * Writes a standardized, multi-line section banner.
     */
    private function writeSectionBanner(string $message, OutputInterface $output, string $char = '='): void
    {
        $output->writeln('');
        $output->writeln(str_repeat($char, 70));
        $output->writeln($message);
        $output->writeln(str_repeat($char, 70));
        $output->writeln('');
    }

    /**
     * Writes a simple, formatted info message.
     */
    private function writeInfoMessage(string $message, OutputInterface $output): void
    {
        $output->writeln("");
        $output->writeln("😊  <info>{$message}</info>");
        $output->writeln("");
    }

    /**
     * Writes the final success message with randomly chosen emojis.
     */
    private function writeSuccessMessage(string $message, OutputInterface $output): void
    {
        $output->writeln("\n\nFinished!\n");

        $emojiSets = [
            ['✨', '🧡'],
            ['🌟', '💚'],
            ['🎉', '💙'],
            ['🚀', '💜'],
            ['🍀', '💛'],
            ['🔥', '💖'],
        ];
        $chosen = $emojiSets[array_rand($emojiSets)];
        $border = str_repeat($chosen[0], 17) . ' ' . $chosen[1] . ' ' . str_repeat($chosen[0], 17);
        $output->writeln("");
        $output->writeln($border);
        $output->writeln('');
        $output->writeln("<info>{$message}</info>");
        $output->writeln('');
        $output->writeln($border);
        $output->writeln("");
    }

    /**
     * Picks a random success message from the keep-calm-messages.txt file and prints it.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param bool $isError
     */
    protected function writeRandomMessageAtTheEnd(OutputInterface $output, bool $isError): void
    {
        $provider = new KeepCalmMessagesProvider();
        $message = $provider->getRandomMessage();

        if ($isError) {
            $message = "<error>😥 "
                     . "Some commands did not complete successfully. Please check the output above.</error>"
                     . "\nbut ... 😎 " . $message;
        }

        $this->writeSuccessMessage($message, $output);
    }

    /**
     * Prints a checklist summary of all executed/skipped commands.
     */
    private function writeCommandSummaryChecklist(OutputInterface $output, array $executedCommands): void
    {
        $output->writeln("\n<info>Command Execution Summary:</info>");
        foreach ($executedCommands as $cmd) {
            if ($cmd['reason'] === 'Command not found') {
                continue;
            }
            $statusIcon = [
                'success' => '✅',
                'failed' => '❌',
                'skipped' => '⏭️',
            ][$cmd['status']] ?? '❓';
            $reason = $cmd['reason'] ? " (<comment>{$cmd['reason']}</comment>)" : '';
            $output->writeln("  {$cmd['index']}. {$statusIcon} <comment>{$cmd['name']}</comment>  {$reason}");
        }
        $output->writeln("");
    }
}
