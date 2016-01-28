<?php

namespace N98\Magento\Command\Developer\Console;

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
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->setDescription('Creates a model class')
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
            $modelFileName = $this->getNormalizedPathByArgument($input->getArgument('classpath'));
            $classNameToGenerate = $this->getCurrentModuleNamespace()
                . '\\Model\\'
                . $this->getNormalizedClassnameByArgument($input->getArgument('classpath'));
            $filePathToGenerate = 'Model/' . $modelFileName . '.php';

            $classGenerator = $this->create(ClassGenerator::class);

            /** @var $classGenerator ClassGenerator */
            $classGenerator->setExtendedClass('AbstractModel');
            $classGenerator->addUse('Magento\Framework\Model\AbstractModel');

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
