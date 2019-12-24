<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Code\Generator\ClassGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;

/**
 * Class MakeBlockCommand
 * @package N98\Magento\Command\Developer\Console
 */
class MakeBlockCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:block')
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->setDescription('Creates a generic block class');
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
        $this->createClassFile($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\FileSystemException
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
        $classGenerator->addUse('Magento\Framework\View\Element\Template');

        if (version_compare($this->getMagentoVersion()->getVersion(), '2.2.0', '<')) {
            $classGenerator->setExtendedClass('Template');
        } else {
            $classGenerator->setExtendedClass('Magento\Framework\View\Element\Template');
        }

        $classGenerator->setName($classNameToGenerate);

        $fileGenerator = FileGenerator::fromArray(
            [
                'classes' => [$classGenerator],
            ]
        );

        $directoryWriter = $this->getCurrentModuleDirectoryWriter();
        $directoryWriter->writeFile($filePathToGenerate, $fileGenerator->generate());

        $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
    }
}
