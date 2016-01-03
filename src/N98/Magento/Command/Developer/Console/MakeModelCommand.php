<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;

class MakeModelCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:model')
            ->addArgument('path', InputArgument::REQUIRED)
            ->setDescription('Creates a model')
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
        try {
            $modelFileName = $this->getNormalizedPathByArgument($input->getArgument('path'));
            $classNameToGenerate = $this->getCurrentModuleNamespace()
                . '\\Model\\'
                . $this->getNormalizedClassnameByArgument($input->getArgument('path'));
            $filePathToGenerate = 'Model/' . $modelFileName . '.php';

            $classGenerator = $this->create(ClassGenerator::class);

            /** @var $classGenerator ClassGenerator */
            $classGenerator->setExtendedClass('\Magento\Catalog\Model\AbstractModel');

            $classGenerator->setName($classNameToGenerate);

            $modelFileGenerator = FileGenerator::fromArray(
                [
                    'classes' => [$classGenerator]
                ]
            );

            $directoryWriter = $this->getCurrentModuleDirectoryWriter();
            $directoryWriter->writeFile($filePathToGenerate, $modelFileGenerator->generate());

            $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

}
