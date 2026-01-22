<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Mcp\Server;

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Mcp\CommandToolHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StartCommand extends AbstractMagentoCommand
{
    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('mcp:server:start')
            ->setDescription('Start an MCP server exposing all n98-magerun2 commands as tools');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \N98\Magento\Application $application */
        $application = $this->getApplication();
        $application->setAutoExit(false);
        $logOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : null;

        $builder = Server::builder()
            ->setServerInfo(
                name: 'n98-magerun2 MCP',
                version: $application->getVersion(),
                description: 'MCP server that exposes n98-magerun2 commands as tools'
            )
            ->setInstructions(
                'Each tool name matches an n98-magerun2 command. Pass CLI arguments as a single string, without the command name.'
            );

        $commands = $application->all();
        ksort($commands);

        $toolNames = [];

        foreach ($commands as $commandName => $command) {
            if ($command->isHidden() || $commandName === $this->getName()) {
                continue;
            }

            $description = $command->getDescription();
            if ($description === '') {
                $description = sprintf('Run the "%s" command.', $commandName);
            }

            $description .= ' Arguments: provide CLI arguments as a single string (without the command name).';

            $builder->addTool(
                handler: function (string $arguments = '') use ($application, $commandName): string {
                    $handler = new CommandToolHandler($application, $commandName);

                    return $handler($arguments);
                },
                name: $commandName,
                description: $description
            );

            $toolNames[] = $commandName;
        }

        $server = $builder->build();
        if ($logOutput !== null && $logOutput->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $logOutput->writeln(sprintf(
                '<info>MCP server started (%d tools registered).</info>',
                count($toolNames)
            ));

            if ($logOutput->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                foreach ($toolNames as $toolName) {
                    $logOutput->writeln(sprintf('<info>Registered tool:</info> %s', $toolName));
                }
            }
        }
        $server->run(new StdioTransport());

        return Command::SUCCESS;
    }
}
