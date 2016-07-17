<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Module\Dir;
use N98\Magento\Command\Developer\Console\Util\Config\DiFileWriter;
use N98\Util\BinaryString;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;

class MakeCommandCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:command')
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->addArgument('command_name', InputArgument::OPTIONAL)
            ->setDescription('Creates a cli command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandFileName = $this->getNormalizedPathByArgument($input->getArgument('classpath'));
        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\Console\\Command\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument('classpath'));

        // always suffix class names with "Command"
        if (!BinaryString::endsWith($classNameToGenerate, 'Command')) {
            $classNameToGenerate .= 'Command';
        }

        // always suffix file names with "Command"
        if (!BinaryString::endsWith($commandFileName, 'Command')) {
            $commandFileName .= 'Command';
        }
        $filePathToGenerate = 'Console/Command/' . $commandFileName . '.php';

        $classGenerator = $this->create(ClassGenerator::class);
        /** @var $classGenerator ClassGenerator */
        $classGenerator->setName($classNameToGenerate);
        $classGenerator->setExtendedClass('Command');
        $classGenerator->addUse('Symfony\Component\Console\Command\Command');
        $classGenerator->addUse('Symfony\Component\Console\Input\InputInterface');
        $classGenerator->addUse('Symfony\Component\Console\Output\OutputInterface');
        $classGenerator->addUse('Symfony\Component\Console\Command\Command');

        $commandName = $this->prepareCommandName($input);

        $this->addConfigureMethod($classGenerator, $commandName);
        $this->addExecuteMethod($classGenerator);

        // Write class
        $this->writeClassToFile($output, $classGenerator, $filePathToGenerate);

        // new class to di config
        $this->writeNewCommandToDiConfig($input, $classNameToGenerate);
    }

    /**
     * @param ClassGenerator $classGenerator
     * @param string $commandName
     */
    private function addConfigureMethod(ClassGenerator $classGenerator, $commandName)
    {
        $methodConfigureBody = <<<BODY
\$this->setName('$commandName');
\$this->setDescription('$commandName');
BODY;
;
        $classGenerator->addMethod(
            'configure',
            [],
            MethodGenerator::FLAG_PUBLIC,
            $methodConfigureBody,
            'Configures the current command.'
        );
    }

    /**
     * @param ClassGenerator $classGenerator
     */
    private function addExecuteMethod(ClassGenerator $classGenerator)
    {
        $docblock = DocBlockGenerator::fromArray(array(
            'shortDescription' => '',
            'longDescription'  => '',
            'tags'             => array(
                array(
                    'name'        => 'param',
                    'description' => 'InputInterface $input An InputInterface instance',
                ),
                array(
                    'name'        => 'param',
                    'description' => 'OutputInterface $output An OutputInterface instance',
                ),
                array(
                    'name'        => 'return',
                    'description' => 'null|int null or 0 if everything went fine, or an error code',
                ),
            ),
        ));

        $classGenerator->addMethod(
            'execute',
            [
                [
                    'name' => 'input',
                    'type' => 'InputInterface',
                ],
                [
                    'name' => 'output',
                    'type' => 'OutputInterface',
                ],
            ],
            MethodGenerator::FLAG_PUBLIC,
            '$output->writeln(\'' . $classGenerator->getName() . '\');',
            $docblock
        );
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    private function prepareCommandName(InputInterface $input)
    {
        $commandName = $input->getArgument('command_name');

        if (empty($commandName)) {
            $commandName = strtolower(str_replace('\\', ':', $this->getCurrentModuleNamespace())) . ':';
            $commandName .= str_replace('.', ':', $input->getArgument('classpath'));
        }

        return $commandName;
    }

    /**
     * @param InputInterface $input
     * @param $classNameToGenerate
     */
    private function writeNewCommandToDiConfig(InputInterface $input, $classNameToGenerate)
    {
        $diPath = $this->getCurrentModuleFilePath('etc/di.xml');

        $configWriter = $this->createDiFileWriter($diPath);
        /** @var $configWriter DiFileWriter */

        $configWriter->addConsoleCommand(
            str_replace(
                '\\',
                '',
                $this->getCurrentModuleNamespace() .
                $this->getNormalizedClassnameByArgument($input->getArgument('classpath'))
            ),
            $classNameToGenerate
        );

        $configWriter->save($diPath);
    }

    /**
     * @param $diPath
     * @return Util\Config\FileWriter
     */
    protected function createDiFileWriter($diPath)
    {
        $configWriter = DiFileWriter::createByFilepath($diPath);
        return $configWriter;
    }
}
