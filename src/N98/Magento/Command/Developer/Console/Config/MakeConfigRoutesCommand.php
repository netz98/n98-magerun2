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

use N98\Magento\Command\Developer\Console\Util\Xml;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeConfigRoutesCommand extends AbstractSimpleConfigFileGeneratorCommand
{
    const CONFIG_FILENAME = 'routes.xml';

    protected function configure()
    {
        $this
            ->setName('make:config:routes')
            ->addArgument('area', InputArgument::OPTIONAL, 'Area of routes.xml file', 'frontend')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type', 'standard')
            ->addOption('frontname', 'f', InputOption::VALUE_OPTIONAL, 'Frontname')
            ->setDescription('Creates a new routes.xml file')
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

        $referenceConfigFileContent = file_get_contents(__DIR__ . '/_files/reference_routes.xml');

        if ($input->getOption('frontname') !== null) {
            // add route
            $referenceConfigFileContent = $this->addRoute(
                $referenceConfigFileContent,
                $input->getOption('type'),
                $input->getOption('frontname')
            );
        }

        $referenceConfigFileContent = Xml::formatString($referenceConfigFileContent);
        $this->getCurrentModuleDirectoryWriter()->writeFile($relativeConfigFilePath, $referenceConfigFileContent);

        $output->writeln('<info>generated </info><comment>' . $relativeConfigFilePath . '</comment>');
    }

    /**
     * @param string $xml
     * @param string $type
     * @param string $frontname
     *
     * @return string
     */
    private function addRoute($xml, $type, $frontname)
    {
        /*<router id="standard">
            <route id="vendorExample" frontName="example">
                <module name="Vendor_Example" />
            </route>
        </router>*/

        $xmlObj = simplexml_load_string($xml);

        $routeId = $this->getCurrentModuleId();
        $moduleName = $this->getCurrentModuleName();

        $xmlObj = Xml::addSimpleXmlNodesByXPath($xmlObj,
            "router[@id=$type]/" .
            "route[@id=$routeId,@frontName=$frontname]/" .
            "module[@name=$moduleName]"
        );

        return $xmlObj->asXML();
    }
}