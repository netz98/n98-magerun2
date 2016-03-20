<?php

namespace N98\Magento\Command\Generation;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $finder = Finder::create()
            ->directories()
            ->depth(0)
            ->in($this->getApplication()->getMagentoRootFolder() . '/var/generation');

        $vendorNameToFilter = $input->getArgument('vendorName');

        $filesystem = new Filesystem();

        foreach ($finder as $directory) {
            if (!empty($vendorNameToFilter) && $directory->getBasename() != $vendorNameToFilter) {
                continue;
            }

            /* @var $directory \Symfony\Component\Finder\SplFileInfo */
            $filesystem->recursiveRemoveDirectory($directory->getPathname());
            $output->writeln('<info>Removed <comment>' . $directory->getBasename() . '</comment> folder</info>');
        }
    }
}
