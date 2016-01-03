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

class MakeControllerCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->addArgument('action', InputArgument::REQUIRED)
            ->addOption('result', 'r', InputOption::VALUE_OPTIONAL, 'Result type', 'json')
            ->setDescription('Creates a controller')
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
            $actionFileName = $this->getNormalizedPathByArgument($input->getArgument('action'));
            $classNameToGenerate = $this->getCurrentModuleNamespace()
                . '\\Controller\\'
                . $this->getNormalizedClassnameByArgument($input->getArgument('action'));
            $filePathToGenerate = 'Controller/' . $actionFileName . '.php';

            $classGenerator = $this->create(ClassGenerator::class);

            /** @var $classGenerator ClassGenerator */
            $classGenerator->setExtendedClass('\Magento\Framework\App\Action\Action');

            $body = $this->createClassBody($input);
            $executeMethodDefinition = $this->createClassMethodDefinitions($body);

            $classGenerator->addMethods([$executeMethodDefinition]);
            $classGenerator->setName($classNameToGenerate);
            $classGenerator->addUse('Magento\Framework\Controller\ResultFactory');

            $controllerFileGenerator = FileGenerator::fromArray(
                [
                    'classes' => [$classGenerator]
                ]
            );

            $directoryWriter = $this->getCurrentModuleDirectoryWriter();
            $directoryWriter->writeFile($filePathToGenerate, $controllerFileGenerator->generate());

            $output->writeln('<info>generated </info><comment>' . $filePathToGenerate . '</comment>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    private function createClassBody(InputInterface $input)
    {
        if ($input->getOption('result') == ResultFactory::TYPE_PAGE) {
            $body = 'return $this->resultFactory->create(ResultFactory::TYPE_PAGE);';
            return $body;
        } elseif ($input->getOption('result') == ResultFactory::TYPE_RAW) {
            $body = '$result = $this->resultFactory->create(ResultFactory::TYPE_RAW);';
            $body .= PHP_EOL;
            $body .= '$result->setContents(\'ok\')';
            $body .= PHP_EOL;
            $body .= PHP_EOL;
            $body .= 'return $result';
            return $body;
        } else {
            $body = '$result = $this->resultFactory->create(ResultFactory::TYPE_JSON);';
            $body .= PHP_EOL;
            $body .= '$result->setData(\'ok\')';
            $body .= PHP_EOL;
            $body .= PHP_EOL;
            $body .= 'return $result';
            return $body;
        }
    }

    /**
     * @param $body
     * @return array
     */
    private function createClassMethodDefinitions($body)
    {
        $executeMethodDefinition = [
            'name' => 'execute',
            'parameters' => [],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Dispatch request',
                'tags' => [
                    [
                        'name' => 'return',
                        'description' => '\Magento\Framework\Controller\ResultInterface|ResponseInterface',
                    ],
                    [
                        'name' => 'throws',
                        'description' => '\Magento\Framework\Exception\NotFoundException'
                    ]
                ],
            ],
        ];
        return $executeMethodDefinition;
    }


}