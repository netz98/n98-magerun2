<?php

namespace N98\Magento\Command\System\Store\Config;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class BaseUrlListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $storeManager;

    protected function configure()
    {
        $this
            ->setName('sys:store:config:base-url:list')
            ->setDescription('Lists all base urls')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function inject(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$input->getOption('format')) {
            $this->writeSection($output, 'Magento Stores - Base URLs');
        }
        $this->initMagento();


        foreach ($this->storeManager->getStores() as $store) {
            $table[$store->getId()] = array(
                $store->getId(),
                $store->getCode(),
                $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, true)
            );
        }

        ksort($table);
        $this->getHelper('table')
            ->setHeaders(array('id', 'code', 'unsecure_baseurl', 'secure_baseurl'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
