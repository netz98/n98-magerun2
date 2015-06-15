<?php

namespace N98\Magento\Command\Generation;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Filesystem;
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
      ;
    }

   /**
    * @param \Symfony\Component\Console\Input\InputInterface $input
    * @param \Symfony\Component\Console\Output\OutputInterface $output
    * @return int|void
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $finder = Finder::create()
            ->directories()
            ->depth(1)
            ->in($this->getApplication()->getMagentoRootFolder() . '/var/generation')
        ;

        $filesystem = new Filesystem();

        foreach ($finder as $directory) {
            /* @var $directory \Symfony\Component\Finder\SplFileInfo */
            $filesystem->recursiveRemoveDirectory($directory->getPathname());
        }
    }
}