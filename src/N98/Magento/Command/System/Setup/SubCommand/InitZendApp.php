<?php

namespace N98\Magento\Command\System\Setup\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Magento\Command\System\Setup\BridgetConsoleLogger;

class InitZendApp extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $zendApplication = \Zend\Mvc\Application::init(
            require $this->getMagentoRootFolder() . '/setup/config/application.config.php'
        );

        $serviceManager = $zendApplication->getServiceManager();
        $this->config->setObject('zendServiceManager', $serviceManager);

        $setupFactory = $serviceManager->get('Magento\Setup\Module\SetupFactory');
        /* @var $setupFactory \Magento\Setup\Module\SetupFactory */
        $this->config->setObject('setupFactory', $setupFactory);

        $moduleList = $this->getCommand()
            ->getApplication()
            ->getObjectManager()->get('Magento\Framework\Module\ModuleListInterface');

        /* @var $modules \Magento\Framework\Module\ModuleListInterface */
        $this->config->setArray('moduleNames', $moduleList->getNames());

        $logger = new BridgetConsoleLogger($this->output);
        /* @var $logger \Magento\Setup\Model\LoggerInterface */
        $this->config->setObject('logger', $logger);

        return true;
    }

    /**
     * @return string
     */
    private function getMagentoRootFolder()
    {
        return $this->getCommand()->getApplication()->getMagentoRootFolder();
    }
}
