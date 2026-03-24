<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\MageForge;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends AbstractMagentoCommand
{
    protected function configure(): void
    {
        $this
            ->setName('mageforge:install')
            ->setDescription('Install the MageForge tool')
            ->addOption(
                'no-check',
                null,
                InputOption::VALUE_NONE,
                'Skip the post-install compatibility check'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $magentoRootFolder = $this->getApplication()->getMagentoRootFolder();

        $output->writeln('<info>Installing MageForge via Composer...</info>');
        $composerProcess = new Process(
            ['composer', 'require', 'openforgeproject/mageforge'],
            $magentoRootFolder
        );
        $composerProcess->setTimeout(300);
        $composerProcess->run(function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if (!$composerProcess->isSuccessful()) {
            $output->writeln('<error>composer require failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Enabling OpenForgeProject_MageForge module...</info>');
        $enableProcess = new Process(
            ['bin/magento', 'module:enable', 'OpenForgeProject_MageForge'],
            $magentoRootFolder
        );
        $enableProcess->run(function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if (!$enableProcess->isSuccessful()) {
            $output->writeln('<error>module:enable failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Running setup:upgrade...</info>');
        $upgradeProcess = new Process(
            ['bin/magento', 'setup:upgrade'],
            $magentoRootFolder
        );
        $upgradeProcess->setTimeout(300);
        $upgradeProcess->run(function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if (!$upgradeProcess->isSuccessful()) {
            $output->writeln('<error>setup:upgrade failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>MageForge installed successfully.</info>');

        if ($input->getOption('no-check')) {
            return Command::SUCCESS;
        }

        $output->writeln('<info>Running MageForge Hyva compatibility check...</info>');
        $this->getApplication()->setAutoExit(false);
        $checkInput = new StringInput('mageforge:hyva:compatibility:check');
        $checkInput->setInteractive(false);

        return $this->getApplication()->run($checkInput, $output);
    }
}
