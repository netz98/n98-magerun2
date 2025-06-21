<?php

namespace N98\Magento\Command\Developer;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State as AppState;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class KeepCalmCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    protected function configure(): void
    {
        $this
            ->setName('dev:keep-calm')
            ->setDescription(
                'Run cache clean, reindex, setup upgrade, di compile and static content deploy (experimental)'
            )
            ->addOption(
                'force-static-content-deploy',
                null,
                InputOption::VALUE_NONE,
                'Force static content deploy with --force option, even in developer/default mode.'
            );
    }

    /**
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @return void
     */
    public function inject(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $forceStaticContentDeploy = $input->getOption('force-static-content-deploy');
        $commands = [
            'setup:upgrade', // also clears cache
            'indexer:reindex',
            'setup:di:compile',
            'setup:static-content:deploy',
        ];

        // Manual static content deployment is not required in "default" and "developer" modes.
        $skipStaticContentDeploy = in_array(
            $this->deploymentConfig->get(AppState::PARAM_MODE),
            [AppState::MODE_DEFAULT, AppState::MODE_DEVELOPER],
            true
        );

        foreach ($commands as $commandName) {

            $commandInput = new StringInput($commandName);

            /**
             * Special handling for setup:static-content:deploy command
             */
            if ($commandName === 'setup:static-content:deploy') {
                if ($skipStaticContentDeploy && !$forceStaticContentDeploy) {
                    continue;
                }

                $commandInput = $forceStaticContentDeploy
                    ? new StringInput('setup:static-content:deploy --force')
                    : new StringInput('setup:static-content:deploy');
            }

            $command = $this->getApplication()->find($commandName);
            $exitCode = $command->run($commandInput, $output);

            if (Command::SUCCESS !== $exitCode) {
                $output->writeln(
                    sprintf(
                        '<error>Command "%s" failed with exit code %d</error>',
                        $commandName,
                        $exitCode
                    )
                );
                return $exitCode;
            }
        }

        return Command::SUCCESS;
    }
}
