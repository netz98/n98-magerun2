<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FlushCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('dev:flush')
            ->setDescription('Clean integration test sandbox var directories')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Delete entire integration test sandboxes')
            ->setHelp(
                'Remove var directories in dev/tests/integration/tmp/sandbox-* only. '
                . 'Use --force to remove entire sandboxes. Symbolic links at deletion boundaries are refused. '
                . 'Stop integration tests before running this command. No database is modified.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->detectMagento($output);

        return $this->flushSandboxes(
            $this->_magentoRootFolder,
            (bool) $input->getOption('force'),
            $output
        );
    }

    /**
     * Keep discovery bounded to the integration tmp directory; never follow directory links.
     */
    protected function flushSandboxes(string $magentoRoot, bool $force, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $removed = 0;
        $failed = false;

        try {
            $tmp = realpath($magentoRoot);
            if ($tmp === false || !is_dir($tmp)) {
                throw new \RuntimeException('Magento root is not a directory.');
            }

            foreach (['dev', 'tests', 'integration', 'tmp'] as $component) {
                $tmp .= DIRECTORY_SEPARATOR . $component;
                if (is_link($tmp)) {
                    throw new \RuntimeException('Refusing symbolic link: ' . $tmp);
                }
                if (!file_exists($tmp)) {
                    $output->writeln('<info>No integration test sandboxes found.</info>');

                    return Command::SUCCESS;
                }
                if (!is_dir($tmp)) {
                    throw new \RuntimeException('Expected a directory: ' . $tmp);
                }
            }

            foreach (new \FilesystemIterator($tmp, \FilesystemIterator::SKIP_DOTS) as $entry) {
                if (!preg_match('/^sandbox-.+$/D', $entry->getFilename())) {
                    continue;
                }

                try {
                    $sandbox = $entry->getPathname();
                    if (is_link($sandbox)) {
                        throw new \RuntimeException('Refusing symbolic link: ' . $sandbox);
                    }
                    if (!$entry->isDir()) {
                        continue;
                    }

                    $target = $force ? $sandbox : $sandbox . DIRECTORY_SEPARATOR . 'var';
                    if (is_link($target)) {
                        throw new \RuntimeException('Refusing symbolic link: ' . $target);
                    }
                    if (!file_exists($target)) {
                        continue;
                    }
                    if (!is_dir($target)) {
                        throw new \RuntimeException('Expected a directory: ' . $target);
                    }

                    // Symfony removes nested links themselves, not their targets.
                    $filesystem->remove($target);
                    ++$removed;
                    $output->writeln('<info>Removed ' . OutputFormatter::escape($target) . '</info>');
                } catch (\Exception $exception) {
                    $failed = true;
                    $output->writeln('<error>' . OutputFormatter::escape($exception->getMessage()) . '</error>');
                }
            }
        } catch (\Exception $exception) {
            $output->writeln('<error>' . OutputFormatter::escape($exception->getMessage()) . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Removed %d integration test %s.</info>', $removed, $force ? 'sandboxes' : 'var directories'));

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
