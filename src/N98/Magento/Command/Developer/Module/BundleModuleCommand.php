<?php

namespace N98\Magento\Command\Developer\Module;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class BundleModuleCommand extends AbstractMagentoCommand
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    protected function configure()
    {
      $this
          ->setName('dev:module:bundle')
          ->setDescription('Bundle a module')
          ->addOption('dry-run', 'd', InputOption::VALUE_OPTIONAL|InputOption::VALUE_NONE, "Don't write file.")
          ->addArgument('module', InputArgument::REQUIRED, 'Module name like Vendor_Acme')
      ;
    }

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function inject(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

   /**
    * @param \Symfony\Component\Console\Input\InputInterface $input
    * @param \Symfony\Component\Console\Output\OutputInterface $output
    * @return int|void
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleName = $input->getArgument('module');

        if (empty($moduleName)) {
            throw new \InvalidArgumentException('No module defined');
        }

        $this->detectMagento($output);
        if ($this->initMagento()) {
            $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);

            if (empty($modulePath)) {
                throw new \InvalidArgumentException(sprintf('Module "%s" was not found', $moduleName));
            }

            $output->writeln('<info>Module source:</info> <comment>' . $modulePath . '</comment>');
            $outputFilename = getcwd() . '/' . $moduleName . '.zip';

            $zipArchive = new \ZipArchive();

            if (!$zipArchive->open($outputFilename, \ZIPARCHIVE::CREATE)) {
                throw new \RuntimeException('Cannot open/create zip file');
            }

            $finder = Finder::create()
                ->files()
                ->in($modulePath)
                ->ignoreVCS(true)
                ->ignoreDotFiles(true);

            foreach ($finder as $name => $file) {
                /** @var SplFileInfo $file */

                if ($file->isDir()) {
                    continue;
                }

                $output->writeln('- ' . $file->getRelativePathname());
                $zipArchive->addFile($file->getRealPath(), $file->getRelativePathname());
            }


            if (!$zipArchive->status == \ZIPARCHIVE::ER_OK) {
                throw new \RuntimeException('Failed to write local files to zip');
            }

            $zipArchive->close();

            $output->writeln('<info>Created archive:</info> <comment>' . $outputFilename . '</comment>');
        }
    }
}
