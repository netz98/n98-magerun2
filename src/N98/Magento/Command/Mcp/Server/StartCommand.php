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
use N98\Magento\Command\MagentoCoreProxyCommand;
use N98\Magento\Mcp\CommandPatternResolver;
use N98\Magento\Mcp\CommandToolHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Start an MCP server exposing selected n98-magerun2 commands as tools')
            ->addOption(
                'include',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Command include filter. Supports wildcards and @group references (for example: "sys:cron:* @maintenance").'
            )
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Command exclude filter. Supports wildcards and @group references (for example: "dev:* @unsafe").'
            );
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

        $patternResolver = new CommandPatternResolver();
        $commandGroups = $patternResolver->getCommandGroupDefinitions($this->getCommandConfig());
        $includePatterns = $this->resolveFilterPatterns(
            $input->getOption('include'),
            $patternResolver,
            $commandGroups
        );
        $excludePatterns = $this->resolveFilterPatterns(
            $input->getOption('exclude'),
            $patternResolver,
            $commandGroups
        );
        $internalCommands = ['help', 'list', 'completion'];

        $toolNames = [];

        foreach ($commands as $commandName => $command) {
            if ($command->isHidden() || $commandName === $this->getName()) {
                continue;
            }

            if ($commandName !== $command->getName()) {
                continue;
            }

            if (in_array($commandName, $internalCommands, true)) {
                continue;
            }

            if ($command instanceof MagentoCoreProxyCommand && !$this->matchesAnyPattern($commandName, $includePatterns)) {
                continue;
            }

            if (!empty($includePatterns) && !$this->matchesAnyPattern($commandName, $includePatterns)) {
                continue;
            }

            if ($this->matchesAnyPattern($commandName, $excludePatterns)) {
                continue;
            }

            $description = $command->getDescription();
            if ($description === '') {
                $description = sprintf('Run the "%s" command.', $commandName);
            }

            $description .= ' Arguments: provide CLI arguments as a single string (without the command name).';

            $toolName = str_replace(':', '_', $commandName);

            $builder->addTool(
                handler: function (string $arguments = '') use ($application, $commandName): string {
                    $handler = new CommandToolHandler($application, $commandName);

                    return $handler($arguments);
                },
                name: $toolName,
                description: $description
            );

            $toolNames[] = $toolName;
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

    public function getHelp(): string
    {
        return parent::getHelp() . PHP_EOL . $this->getCommandGroupHelp();
    }

    /**
     * @param string|string[]|null $rawFilter
     * @param array<string, array{commands: string[], description: string}> $commandGroups
     * @return string[]
     */
    private function resolveFilterPatterns($rawFilter, CommandPatternResolver $patternResolver, array $commandGroups): array
    {
        if ($rawFilter === null || $rawFilter === false) {
            return [];
        }

        $entries = [];
        foreach ((array) $rawFilter as $item) {
            if (!is_string($item)) {
                continue;
            }

            $parts = preg_split('~[\s,]+~', trim($item), -1, PREG_SPLIT_NO_EMPTY);
            if ($parts === false) {
                continue;
            }

            $entries = array_merge($entries, $parts);
        }

        if (empty($entries)) {
            return [];
        }

        return $patternResolver->resolvePatterns($entries, $commandGroups);
    }

    /**
     * @param string[] $patterns
     */
    private function matchesAnyPattern(string $commandName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === $commandName || fnmatch($pattern, $commandName)) {
                return true;
            }
        }

        return false;
    }

    private function getCommandGroupHelp(): string
    {
        $messages = PHP_EOL;
        $messages .= "<comment>Available Command Groups</comment>\n\n";

        $patternResolver = new CommandPatternResolver();
        $groups = $patternResolver->getCommandGroupDefinitions($this->getCommandConfig());
        if (empty($groups)) {
            return $messages . " <info>(none configured)</info>\n";
        }

        $maxNameLen = 0;
        $list = [];
        foreach ($groups as $id => $definition) {
            $name = '@' . $id;
            $description = $definition['description'] !== '' ? $definition['description'] . '.' : '';
            $patternPreview = implode(' ', $definition['commands']);
            if ($patternPreview !== '') {
                $description .= ($description !== '' ? ' ' : '') . sprintf('Patterns: %s', $patternPreview);
            }

            $nameLen = strlen($name);
            if ($nameLen > $maxNameLen) {
                $maxNameLen = $nameLen;
            }

            $list[] = [$name, $description];
        }

        foreach ($list as $entry) {
            [$name, $description] = $entry;
            $delta = max(0, $maxNameLen - strlen($name));
            $messages .= sprintf(" <info>%s</info>%s  %s\n", $name, str_repeat(' ', $delta), $description);
        }

        return $messages;
    }
}
