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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class StartCommand extends AbstractMagentoCommand
{
    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('mcp:server:start')
            ->setDescription('Start an MCP server exposing all n98-magerun2 commands as tools')
            ->addOption(
                'transport',
                't',
                InputOption::VALUE_REQUIRED,
                'Transport mode: "stdio" or "http"',
                'stdio'
            )
            ->addOption(
                'address',
                'a',
                InputOption::VALUE_REQUIRED,
                'Address to bind to (HTTP mode only)',
                '127.0.0.1'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Port to listen on (HTTP mode only)',
                '8098'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $transportMode = $input->getOption('transport');

        if ($transportMode === 'http') {
            return $this->startHttpServer($input, $output);
        }

        return $this->startStdioServer($input, $output);
    }

    private function startHttpServer(InputInterface $input, OutputInterface $output): int
    {
        $address = $input->getOption('address');
        $port = $input->getOption('port');

        /** @var \N98\Magento\Application $app */
        $app = $this->getApplication();
        $rootDir = $app->getMagentoRootFolder();

        $routerScript = __DIR__ . '/../../../../../../bin/mcp-router.php';
        if (!file_exists($routerScript)) {
            // Fallback for installed phar/composer structure if needed, but for dev this is fine
            $routerScript = realpath(__DIR__ . '/../../../../../../bin/mcp-router.php');
        }

        if (!$routerScript || !file_exists($routerScript)) {
            $output->writeln('<error>Could not locate bin/mcp-router.php</error>');
            return Command::FAILURE;
        }

        $msg = sprintf("Starting MCP HTTP server at http://%s:%s", $address, $port);
        $output->writeln("<info>$msg</info>");
        if ($rootDir) {
            $output->writeln("<comment>Magento Root: $rootDir</comment>");
        }

        $process = new Process([
            PHP_BINARY,
            '-S',
            sprintf('%s:%s', $address, $port),
            '-t',
            ($rootDir ?: getcwd()), // serve from root or current dir
            $routerScript,
        ]);

        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        // Pass root dir environment variable
        $env = $process->getEnv();
        $env['N98_MAGERUN2_ROOT_DIR'] = $rootDir;
        $process->setEnv($env);

        $process->start(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        $process->wait();

        return $process->getExitCode() ?? Command::SUCCESS;
    }

    private function startStdioServer(InputInterface $input, OutputInterface $output): int
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
        if ($logOutput !== null) {
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
