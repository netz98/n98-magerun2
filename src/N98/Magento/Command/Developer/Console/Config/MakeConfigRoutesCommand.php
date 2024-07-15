<?php

namespace N98\Magento\Command\Developer\Console\Config;

use N98\Magento\Command\Developer\Console\Util\Xml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeConfigRoutesCommand
 * @package N98\Magento\Command\Developer\Console\Config
 */
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
            ->setDescription('Creates a new routes.xml file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $selectedArea = $input->getArgument('area');
        $relativeConfigFilePath = $this->getRelativeConfigFilePath(self::CONFIG_FILENAME, $selectedArea);

        if ($this->getCurrentModuleDirectoryReader()->isExist($relativeConfigFilePath)) {
            $output->writeln('<warning>File already exists. Skiped generation</warning>');

            return Command::SUCCESS;
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

        return Command::SUCCESS;
    }

    /**
     * @param string $xml
     * @param string $type
     * @param string $frontname
     * @return string
     * @throws \Exception
     */
    private function addRoute($xml, $type, $frontname)
    {
        /*<router id="standard">
            <route id="vendorExample" frontName="example">
                <module name="Vendor_Example" />
            </route>
        </router>*/

        $xmlObj = \simplexml_load_string($xml);

        $routeId = $this->getCurrentModuleId();
        $moduleName = $this->getCurrentModuleName();

        $xmlObj = Xml::addSimpleXmlNodesByXPath(
            $xmlObj,
            "router[@id=$type]/route[@id=$routeId,@frontName=$frontname]/module[@name=$moduleName]"
        );

        return $xmlObj->asXML();
    }
}
