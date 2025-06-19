<?php

namespace N98\Magento\Command\Developer\Console;

use Laminas\Code\Generator\FileGenerator;
use Magento\Framework\Code\Generator\ClassGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeModelCommand
 * @package N98\Magento\Command\Developer\Console
 */
class MakeModelCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:model')
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->setDescription('Creates a model class');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function catchedExecute(InputInterface $input, OutputInterface $output)
    {
        $modelFileName = $this->getNormalizedPathByArgument($input->getArgument('classpath'));
        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\Model\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument('classpath'));
        $filePathToGenerate = 'Model/' . $modelFileName . '.php';

        $classGenerator = $this->create(ClassGenerator::class);

        /** @var $classGenerator ClassGenerator */
        $classGenerator->addUse('Magento\\Framework\\Model\\AbstractModel');
        $classGenerator->setExtendedClass('Magento\\Framework\\Model\\AbstractModel');
        $classGenerator->setName($classNameToGenerate);

        $modelFileGenerator = new FileGenerator();
        $modelFileGenerator->setClass($classGenerator);

        $directoryWriter = $this->getCurrentModuleDirectoryWriter();
        $directoryWriter->writeFile($filePathToGenerate, $modelFileGenerator->generate());

        $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
    }
}
