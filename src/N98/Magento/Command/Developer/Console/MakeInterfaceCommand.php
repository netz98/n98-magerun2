<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Code\Generator\InterfaceGenerator;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;

class MakeInterfaceCommand extends AbstractGeneratorCommand
{
    const CLASSPATH = 'classpath';

    protected function configure()
    {
        $this
            ->setName('make:interface')
            ->addArgument(self::CLASSPATH, InputArgument::REQUIRED)
            ->setDescription('Creates a generic interface')
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
            $classFileName = $this->getNormalizedPathByArgument($input->getArgument(self::CLASSPATH));

            $classNameToGenerate = $this->getCurrentModuleNamespace()
                . '\\'
                . $this->getNormalizedClassnameByArgument($input->getArgument(self::CLASSPATH));

            $filePathToGenerate = $classFileName . '.php';

            /** @var $classGenerator InterfaceGenerator */
            $classGenerator = $this->create(InterfaceGenerator::class);
            $classGenerator->setName($classNameToGenerate);

            $fileGenerator = FileGenerator::fromArray([
                'classes' => [$classGenerator]
            ]);

            $directoryWriter = $this->getCurrentModuleDirectoryWriter();
            $directoryWriter->writeFile($filePathToGenerate, $fileGenerator->generate());

            $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

}
