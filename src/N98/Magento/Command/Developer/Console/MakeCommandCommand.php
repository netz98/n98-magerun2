<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Magento\Framework\Code\Generator\ClassGenerator;
use N98\Magento\Command\Developer\Console\Util\Config\DiFileWriter;
use N98\Util\BinaryString;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeCommandCommand
 * @package N98\Magento\Command\Developer\Console
 */
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
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
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

        $classGenerator->addUse('Symfony\Component\Console\Command\Command');
        $classGenerator->addUse('Symfony\Component\Console\Input\InputInterface');
        $classGenerator->addUse('Symfony\Component\Console\Output\OutputInterface');
        $classGenerator->setExtendedClass('Symfony\Component\Console\Command\Command');

        $commandName = $this->prepareCommandName($input);

        $this->addConfigureMethod($classGenerator, $commandName);
        $this->addExecuteMethod($classGenerator);

        // Write class
        $this->writeClassToFile($output, $classGenerator, $filePathToGenerate);

        // new class to di config
        $this->writeNewCommandToDiConfig($input, $classNameToGenerate);

        return Command::SUCCESS;
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
        $docblock = DocBlockGenerator::fromArray([
            'shortDescription' => '',
            'longDescription'  => '',
            'tags'             => [
                [
                    'name'        => 'param',
                    'description' => 'InputInterface $input An InputInterface instance',
                ],
                [
                    'name'        => 'param',
                    'description' => 'OutputInterface $output An OutputInterface instance',
                ],
                [
                    'name'        => 'return',
                    'description' => 'null|int null or 0 if everything went fine, or an error code',
                ],
            ],
        ]);

        $inputParamType = '\Symfony\Component\Console\Input\InputInterface';
        $outputParamType = '\Symfony\Component\Console\Output\OutputInterface';

        $classGenerator->addMethod(
            'execute',
            [
                [
                    'name' => 'input',
                    'type' => $inputParamType,
                ],
                [
                    'name' => 'output',
                    'type' => $outputParamType,
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

        $configWriter->saveFile($diPath);
    }

    /**
     * @param $diPath
     * @return Util\Config\FileWriter
     */
    protected function createDiFileWriter($diPath)
    {
        return DiFileWriter::createByFilepath($diPath);
    }
}
