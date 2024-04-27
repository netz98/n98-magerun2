<?php

namespace N98\Magento\Command\Developer\Module;

use Magento\Framework\Filter\Input;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Manager;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Developer\Module
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var array
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\FullModuleList
     */
    protected $moduleListObject;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @return array
     */
    public function getModuleList()
    {
        return $this->moduleList;
    }

    /**
     * @param FullModuleList $moduleList
     * @param Manager $moduleManager
     */
    public function inject(\Magento\Framework\Module\FullModuleList $moduleList, Manager $moduleManager)
    {
        $this->moduleListObject = $moduleList;
        $this->moduleManager = $moduleManager;
    }

    protected function configure()
    {
        $this
            ->setName('dev:module:list')
            ->addOption(
                'vendor',
                null,
                InputOption::VALUE_OPTIONAL,
                'Show modules of a specific vendor (case insensitive)'
            )
            ->setDescription('List all installed modules')
            ->addOption('only-enabled', 'e', InputOption::VALUE_NONE, 'Show only enabled modules')
            ->addOption('only-disabled', 'd', InputOption::VALUE_NONE, 'Show only disabled modules')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('only-disabled') && $input->getOption('only-enabled')) {
            throw new \Exception('You can only use one of the options --only-enabled or --only-disabled');
        }

        $this->detectMagento($output, true);

        if ($input->getOption('format') == null) {
            $this->writeSection($output, 'Magento Modules');
        }

        $this->initMagento();
        $this->prepareModuleList($input);

        $this->getHelper('table')
            ->setHeaders(['Name', '(Schema) Version', 'Status'])
            ->renderByFormat($output, $this->moduleList, $input->getOption('format'));

        return Command::SUCCESS;
    }

    /**
     * @param string $vendor
     * @return void
     */
    protected function prepareModuleList(InputInterface  $input)
    {
        $this->moduleList = [];

        $vendor = $input->getOption('vendor');

        foreach ($this->moduleListObject->getNames() as $moduleName) {

            $info = $this->moduleListObject->getOne($moduleName);

            // First index is (probably always) vendor
            $moduleNameData = explode('_', $moduleName);

            if ($vendor !== null && strtolower($moduleNameData[0]) !== strtolower($vendor)) {
                continue;
            }

            $isEnabled = $this->moduleManager->isEnabled($moduleName);

            if ($isEnabled && $input->getOption('only-disabled')) {
                continue;
            }

            if (!$isEnabled && $input->getOption('only-enabled')) {
                continue;
            }

            $this->moduleList[] = [
                $info['name'],
                $info['setup_version'],
                $isEnabled ? 'enabled' : 'disabled',
            ];
        }
    }
}
