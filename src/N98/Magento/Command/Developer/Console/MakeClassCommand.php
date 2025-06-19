<?php

namespace N98\Magento\Command\Developer\Console;

use Laminas\Code\Generator\FileGenerator;
use Magento\Framework\Code\Generator\ClassGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeClassCommand
 * @package N98\Magento\Command\Developer\Console
 */
class MakeClassCommand extends AbstractGeneratorCommand
{
    const CLASSPATH = 'classpath';

    protected function configure()
    {
        $this
            ->setName('make:class')
            ->addArgument(self::CLASSPATH, InputArgument::REQUIRED)
            ->setDescription('Creates a generic class');
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
        $classFileName = $this->getNormalizedPathByArgument($input->getArgument(self::CLASSPATH));

        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument(self::CLASSPATH));

        $filePathToGenerate = $classFileName . '.php';

        /** @var $classGenerator ClassGenerator */
        $classGenerator = $this->create(ClassGenerator::class);
        $classGenerator->setName($classNameToGenerate);

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $directoryWriter = $this->getCurrentModuleDirectoryWriter();
        $directoryWriter->writeFile($filePathToGenerate, $fileGenerator->generate());

        $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
    }
}
