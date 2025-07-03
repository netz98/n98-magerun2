<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Generation;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class FlushCommand
 * @package N98\Magento\Command\Generation
 */
class FlushCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('generation:flush')
            ->setDescription('Flushs generated code like factories and proxies')
            ->addArgument('vendorName', InputArgument::OPTIONAL, 'Vendor to remove like "Magento"');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $generationFlushDirectories = [
            $this->getApplication()->getMagentoRootFolder() . '/var/generation',
            $this->getApplication()->getMagentoRootFolder() . '/generated/code',
        ];

        $finder = Finder::create()
            ->directories()
            ->depth(0);

        foreach ($generationFlushDirectories as $directoryToFlush) {
            if (is_dir($directoryToFlush)) {
                $finder->in($directoryToFlush);
            }
        }

        $vendorNameToFilter = $input->getArgument('vendorName');

        $filesystem = new Filesystem();

        foreach ($finder as $directory) {
            if (!empty($vendorNameToFilter) && $directory->getBasename() != $vendorNameToFilter) {
                continue;
            }

            /* @var $directory \Symfony\Component\Finder\SplFileInfo */
            if ($filesystem->recursiveRemoveDirectory($directory->getPathname())) {
                $output->writeln('<info>Removed <comment>' . $directory->getBasename() . '</comment> folder</info>');
            } else {
                $output->writeln('<error>Failed to remove <comment>' . $directory->getBasename() . '</comment> folder</error>');
            }
        }

        return Command::SUCCESS;
    }
}
