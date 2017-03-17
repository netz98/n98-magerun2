<?php

namespace N98\Magento\Command\SearchEngine;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Search\Model\Adminhtml\System\Config\Source\Engine
     */
    private $searchEngineConfig;

    protected function configure()
    {
        $this
            ->setName('search:engine:list')
            ->setDescription('Lists all registered search engines')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Magento\Search\Model\Adminhtml\System\Config\Source\Engine $searchEngineConfig
     */
    public function inject(\Magento\Search\Model\Adminhtml\System\Config\Source\Engine $searchEngineConfig)
    {
        $this->searchEngineConfig = $searchEngineConfig;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $searchEngines = $this->searchEngineConfig->toOptionArray();

        $table = array();
        foreach ($searchEngines as $searchEngine) {
            $table[] = [
                $searchEngine['value'],
                $searchEngine['label'],
            ];
        }
        $this->getHelper('table')
            ->setHeaders(array('code', 'label'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
