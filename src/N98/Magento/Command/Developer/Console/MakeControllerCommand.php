<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Controller\ResultFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends AbstractGeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->addArgument('classpath', InputArgument::REQUIRED)
            ->addOption('result', 'r', InputOption::VALUE_OPTIONAL, 'Result type', 'page')
            ->setDescription('Creates a controller action class');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function catchedExecute(InputInterface $input, OutputInterface $output)
    {
        $actionFileName = $this->getNormalizedPathByArgument($input->getArgument('classpath'));
        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\Controller\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument('classpath'));
        $filePathToGenerate = 'Controller/' . $actionFileName . '.php';

        $classGenerator = $this->create(ClassGenerator::class);

        /** @var $classGenerator ClassGenerator */
        if (version_compare($this->getMagentoVersion()->getVersion(), '2.2.0', '<')) {
            $classGenerator->setExtendedClass('Action');
        } else {
            $classGenerator->setExtendedClass('Magento\Framework\App\Action\Action');
        }

        $body = $this->createClassBody($input);
        $executeMethodDefinition = $this->createClassMethodDefinitions($body);

        $classGenerator->addMethods([$executeMethodDefinition]);
        $classGenerator->setName($classNameToGenerate);
        $classGenerator->addUse('Magento\Framework\App\Action\Action');
        $classGenerator->addUse('Magento\Framework\Controller\ResultFactory');

        $this->writeClassToFile($output, $classGenerator, $filePathToGenerate);

        if ($input->getOption('result') == ResultFactory::TYPE_PAGE) {
            $this->createLayoutFile();
        }
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    private function createClassBody(InputInterface $input)
    {
        $body = '';

        if ($input->getOption('result') == ResultFactory::TYPE_PAGE) {
            $body .= 'return $this->resultFactory->create(ResultFactory::TYPE_PAGE);';
        } elseif ($input->getOption('result') == ResultFactory::TYPE_RAW) {
            $body .= '$result = $this->resultFactory->create(ResultFactory::TYPE_RAW);';
            $body .= PHP_EOL;
            $body .= '$result->setContents(\'ok\');';
            $body .= PHP_EOL;
            $body .= PHP_EOL;
            $body .= 'return $result;';
        } else {
            $body .= '$result = $this->resultFactory->create(ResultFactory::TYPE_JSON);';
            $body .= PHP_EOL;
            $body .= '$result->setData(\'ok\');';
            $body .= PHP_EOL;
            $body .= PHP_EOL;
            $body .= 'return $result;';
        }

        return $body;
    }

    /**
     * @param string $body
     * @return array
     */
    private function createClassMethodDefinitions($body)
    {
        $executeMethodDefinition = [
            'name'       => 'execute',
            'parameters' => [],
            'body'       => $body,
            'docblock'   => [
                'shortDescription' => 'Dispatch request',
                'tags'             => [
                    [
                        'name'        => 'return',
                        'description' => '\Magento\Framework\Controller\ResultInterface|ResponseInterface',
                    ],
                    [
                        'name'        => 'throws',
                        'description' => '\Magento\Framework\Exception\NotFoundException',
                    ],
                ],
            ],
        ];

        return $executeMethodDefinition;
    }

    protected function createLayoutFile()
    {
    }
}
