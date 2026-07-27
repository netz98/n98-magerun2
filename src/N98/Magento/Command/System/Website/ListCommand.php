<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Website;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\System\Website
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
            ->setName('sys:website:list')
            ->setDescription('Lists all websites')
            ->addFormatOption();
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

        if ($input->getOption('format') === null) {
            $this->writeSection($output, 'Magento Websites');
        }

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = $website->getId();
            $table[$websiteId] = [
                $websiteId,
                $website->getCode(),
            ];
        }

        ksort($table);
        $this->getHelper('table')
            ->setHeaders(['id', 'code'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
