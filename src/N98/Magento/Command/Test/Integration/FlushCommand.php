<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Test\Integration;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class FlushCommand
 * @package N98\Magento\Command\Test\Integration
 */
class FlushCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('test:integration:flush')
            ->setDescription('Cleanup integration test temp folders for developer mode')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Completely remove all sandbox directories instead of just cleaning var folders'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $magentoRoot = $this->getApplication()->getMagentoRootFolder();
        if (!$magentoRoot || !is_dir($magentoRoot)) {
            $output->writeln('<error>Could not determine Magento root directory</error>');
            return Command::FAILURE;
        }

        $integrationTestsPath = $magentoRoot . '/dev/tests/integration';

        if (!is_dir($integrationTestsPath)) {
            $output->writeln('<comment>No integration tests directory found at: ' . $integrationTestsPath . '</comment>');
            return Command::SUCCESS;
        }

        $tempPath = $integrationTestsPath . '/tmp';
        if (!is_dir($tempPath)) {
            $output->writeln('<comment>No integration test temp directory found at: ' . $tempPath . '</comment>');
            return Command::SUCCESS;
        }

        $force = $input->getOption('force');
        $filesystem = new Filesystem();
        $processed = 0;
        $errors = 0;

        // Find all sandbox directories
        $finder = Finder::create()
            ->directories()
            ->depth(0)
            ->name('sandbox-*')
            ->in($tempPath);

        if ($finder->count() === 0) {
            $output->writeln('<comment>No sandbox directories found in: ' . $tempPath . '</comment>');
            return Command::SUCCESS;
        }

        foreach ($finder as $sandboxDir) {
            $sandboxPath = $sandboxDir->getPathname();
            $sandboxName = $sandboxDir->getBasename();

            if ($force) {
                // Remove entire sandbox directory
                if ($filesystem->recursiveRemoveDirectory($sandboxPath)) {
                    $output->writeln('<info>Removed sandbox: <comment>' . $sandboxName . '</comment></info>');
                    $processed++;
                } else {
                    $output->writeln('<error>Failed to remove sandbox: <comment>' . $sandboxName . '</comment></error>');
                    $errors++;
                }
            } else {
                // Only clean var folder within sandbox
                $varPath = $sandboxPath . '/var';
                if (is_dir($varPath)) {
                    if ($filesystem->recursiveRemoveDirectory($varPath, true)) {
                        $output->writeln('<info>Cleaned var folder in sandbox: <comment>' . $sandboxName . '</comment></info>');
                        $processed++;
                    } else {
                        $output->writeln('<error>Failed to clean var folder in sandbox: <comment>' . $sandboxName . '</comment></error>');
                        $errors++;
                    }
                } else {
                    $output->writeln('<comment>No var folder found in sandbox: ' . $sandboxName . '</comment>');
                }
            }
        }

        // Summary
        if ($processed > 0) {
            $action = $force ? 'removed' : 'cleaned';
            $target = $force ? 'sandboxes' : 'var folders';
            $output->writeln('<info>Successfully ' . $action . ' ' . $processed . ' ' . $target . '</info>');
        }

        if ($errors > 0) {
            $output->writeln('<error>Encountered ' . $errors . ' errors during cleanup</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
