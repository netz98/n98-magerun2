<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;

class MakeClassCommand extends AbstractGeneratorCommand
{
    const CLASSPATH = 'classpath';

    protected function configure()
    {
        $this
            ->setName('make:class')
            ->addArgument(self::CLASSPATH, InputArgument::REQUIRED)
            ->setDescription('Creates a generic class')
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
            $modelFileName = $this->getNormalizedPathByArgument($input->getArgument(self::CLASSPATH));

            $classNameToGenerate = $this->getCurrentModuleNamespace()
                . '\\'
                . $this->getNormalizedClassnameByArgument($input->getArgument(self::CLASSPATH));

            $filePathToGenerate = $modelFileName . '.php';

            /** @var $classGenerator ClassGenerator */
            $classGenerator = $this->create(ClassGenerator::class);
            $classGenerator->setName($classNameToGenerate);

            $modelFileGenerator = FileGenerator::fromArray([
                'classes' => [$classGenerator]
            ]);

            $directoryWriter = $this->getCurrentModuleDirectoryWriter();
            $directoryWriter->writeFile($filePathToGenerate, $modelFileGenerator->generate());

            $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

}
