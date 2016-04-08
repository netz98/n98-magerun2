<?php
/**
 * netz98 magento module
 *
 * LICENSE
 *
 * This source file is subject of netz98.
 * You may be not allowed to change the sources
 * without authorization of netz98 new media GmbH.
 *
 * @copyright  Copyright (c) 1999-2016 netz98 new media GmbH (http://www.netz98.de)
 * @author netz98 new media GmbH <info@netz98.de>
 * @category N98
 * @package N98\Magento\Command\Developer\Console
 */

namespace N98\Magento\Command\Developer\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeConfigSystemCommand extends AbstractSimpleConfigFileGeneratorCommand
{
    const CONFIG_FILENAME = 'system.xml';

    protected function configure()
    {
        $this
            ->setName('make:config:system')
            ->addArgument('area', InputArgument::OPTIONAL, 'Area of system.xml file', 'adminhtml')
            ->setDescription('Creates a new system.xml file')
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
        $selectedArea = $input->getArgument('area');
        $relativeConfigFilePath = $this->getRelativeConfigFilePath(self::CONFIG_FILENAME, $selectedArea);

        if ($this->getCurrentModuleDirectoryReader()->isExist($relativeConfigFilePath)) {
            $output->writeln('<warning>File already exists. Skiped generation</warning>');

            return;
        }

        $referenceConfigFileContent = file_get_contents(__DIR__ . '/_files/reference_system.xml');
        $this->getCurrentModuleDirectoryWriter()->writeFile($relativeConfigFilePath, $referenceConfigFileContent);

        $output->writeln('<info>generated </info><comment>' . $relativeConfigFilePath . '</comment>');
    }


}