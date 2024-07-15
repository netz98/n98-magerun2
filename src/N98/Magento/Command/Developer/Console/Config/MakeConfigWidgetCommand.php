<?php

namespace N98\Magento\Command\Developer\Console\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeConfigWidgetCommand
 * @package N98\Magento\Command\Developer\Console\Config
 */
class MakeConfigWidgetCommand extends AbstractSimpleConfigFileGeneratorCommand
{
    const CONFIG_FILENAME = 'widget.xml';

    protected function configure()
    {
        $this
            ->setName('make:config:widget')
            ->addArgument('area', InputArgument::OPTIONAL, 'Area of widget.xml file', 'global')
            ->setDescription('Creates a new widget.xml file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $selectedArea = $input->getArgument('area');
        $relativeConfigFilePath = $this->getRelativeConfigFilePath(self::CONFIG_FILENAME, $selectedArea);

        if ($this->getCurrentModuleDirectoryReader()->isExist($relativeConfigFilePath)) {
            $output->writeln('<warning>File already exists. Skiped generation</warning>');

            return Command::SUCCESS;
        }

        $referenceConfigFileContent = file_get_contents(__DIR__ . '/_files/reference_widget.xml');
        $this->getCurrentModuleDirectoryWriter()->writeFile($relativeConfigFilePath, $referenceConfigFileContent);

        $output->writeln('<info>generated </info><comment>' . $relativeConfigFilePath . '</comment>');

        return Command::SUCCESS;
    }
}
