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

class MakeConfigFieldsetCommand extends AbstractSimpleConfigFileGeneratorCommand
{
    const CONFIG_FILENAME = 'fieldset.xml';

    protected function configure()
    {
        $this
            ->setName('make:config:fieldset')
            ->addArgument('area', InputArgument::OPTIONAL, 'Area of menu.xml file', 'global')
            ->setDescription('Creates a new fieldset.xml file')
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

        $referenceConfigFileContent = file_get_contents(__DIR__ . '/_files/reference_fieldset.xml');
        $this->getCurrentModuleDirectoryWriter()->writeFile($relativeConfigFilePath, $referenceConfigFileContent);

        $output->writeln('<info>generated </info><comment>' . $relativeConfigFilePath . '</comment>');
    }


}