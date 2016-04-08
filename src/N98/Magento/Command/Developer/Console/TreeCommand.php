<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TreeCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('tree')
            ->addArgument('subpath', InputArgument::OPTIONAL, 'Show only subpath', '/')
            ->setDescription('Shows directory tree of current context')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getCurrentModuleDirectoryReader();

        $finder = Finder::create();
        $finder
            ->files()
            ->in($reader->getAbsolutePath() . ltrim($input->getArgument('subpath'), '/'));
        
        foreach ($finder as $file) {
            /** @var $file SplFileInfo */
            $formattedRelativePath = $file->getRelativePath() == '' ? '' : $file->getRelativePath() . '/';

            $output->writeln(
                '<info>' . $formattedRelativePath . '</info>'
                . '<comment>' . $file->getFilename() . '</comment>'
            );
        }

    }
}