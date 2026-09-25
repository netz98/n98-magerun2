<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\FastDiCompile;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends AbstractMagentoCommand
{
    protected function configure(): void
    {
        $this
            ->setName('fast-di-compile:install')
            ->setDescription('Install the fast DI compiler replacement')
            ->addOption(
                'no-setup-upgrade',
                null,
                InputOption::VALUE_NONE,
                'Skip running bin/magento setup:upgrade'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $magentoRootFolder = $this->getApplication()->getMagentoRootFolder();

        if (!$this->runProcess(
            ['composer', 'require', 'spmt/magento2-spmt-fast-di-compile'],
            $magentoRootFolder,
            $output,
            'Installing the fast DI compiler module via Composer...',
            'composer require failed. The fast DI compiler was not installed.'
        )) {
            return Command::FAILURE;
        }

        if (!$this->runProcess(
            ['bin/magento', 'module:enable', 'Spmt_FastDiCompile'],
            $magentoRootFolder,
            $output,
            'Enabling Spmt_FastDiCompile module...',
            'module:enable failed. The package remains installed but is not enabled.'
        )) {
            return Command::FAILURE;
        }

        if ($input->getOption('no-setup-upgrade')) {
            $output->writeln('<comment>Skipped setup:upgrade.</comment>');
        } elseif (!$this->runProcess(
            ['bin/magento', 'setup:upgrade'],
            $magentoRootFolder,
            $output,
            'Running setup:upgrade...',
            'setup:upgrade failed. The module is enabled but may not be fully configured.'
        )) {
            return Command::FAILURE;
        }

        if (!$this->runProcess(
            ['vendor/bin/install-fast-di-compile.php'],
            $magentoRootFolder,
            $output,
            'Installing the verified fast-di-compile binary...',
            'Fast DI compiler binary installation failed. Magento will use its standard PHP compiler until a trusted binary is installed.'
        )) {
            return Command::FAILURE;
        }

        $output->writeln('<info>Fast DI compiler installed successfully.</info>');
        $output->writeln('Run <comment>bin/magento setup:di:compile</comment> as usual to use it.');
        $output->writeln('Use <comment>bin/magento setup:di:compile --standard</comment> to use Magento\'s PHP compiler instead.');

        return Command::SUCCESS;
    }

    /** @param list<string> $command */
    private function runProcess(
        array $command,
        string $workingDirectory,
        OutputInterface $output,
        string $startMessage,
        string $failureMessage
    ): bool {
        $output->writeln('<info>' . $startMessage . '</info>');
        $process = new Process($command, $workingDirectory);
        $process->setTimeout(300);
        $process->run(function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if ($process->isSuccessful()) {
            return true;
        }

        $output->writeln('<error>' . $failureMessage . '</error>');

        return false;
    }
}
