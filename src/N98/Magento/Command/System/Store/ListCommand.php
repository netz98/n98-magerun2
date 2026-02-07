<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Store;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\System\Store
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected function configure()
    {
        $this
            ->setName('sys:store:list')
            ->setDescription('Lists all installed store-views')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function inject(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = [];

        foreach ($this->storeManager->getStores(true, true) as $store) {
            /** @var \Magento\Store\Model\Store $store */
            $table[$store->getId()] = [
                $store->getId(),
                $store->getWebsiteId(),
                $store->getStoreGroupId(),
                $store->getName(),
                $store->getCode(),
                $store->getData('sort_order'),
                $store->getIsActive(),
            ];
        }

        ksort($table);

        $format = $input->getOption('format');

        $this->getHelper('table')
            ->setHeaders(['id', 'website_id', 'group_id', 'name', 'code', 'sort_order', 'is_active'])
            ->renderByFormat($output, $table, $format);

        return Command::SUCCESS;
    }
}
