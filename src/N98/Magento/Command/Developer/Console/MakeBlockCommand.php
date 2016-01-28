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

class MakeBlockCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:block')
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->setDescription('Creates a block')
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
            $this->createClassFile($input, $output);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function createClassFile(InputInterface $input, OutputInterface $output)
    {
        $blockFileName = $this->getNormalizedPathByArgument($input->getArgument('classpath'));
        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\Block\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument('classpath'));
        $filePathToGenerate = 'Block/' . $blockFileName . '.php';

        $classGenerator = $this->create(ClassGenerator::class);

        /** @var $classGenerator ClassGenerator */
        $classGenerator->setExtendedClass('Template');
        $classGenerator->addUse('Magento\Framework\View\Element\Template');

        $classGenerator->setName($classNameToGenerate);

        $fileGenerator = FileGenerator::fromArray(
            [
                'classes' => [$classGenerator]
            ]
        );

        $directoryWriter = $this->getCurrentModuleDirectoryWriter();
        $directoryWriter->writeFile($filePathToGenerate, $fileGenerator->generate());

        $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
    }

}
