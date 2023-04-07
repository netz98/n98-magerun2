<?php

namespace N98\Magento\Command\Sales;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SequenceAddCommand extends AbstractMagentoCommand
{

    protected function configure()
    {
        $this->setName('sales:sequence:add')
            ->setDescription('Add the sequence tables and metadata for given store or all stores')
            ->addArgument('store', InputArgument::OPTIONAL, 'Store code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $storeCode = $input->getArgument('store');

        $di = $this->getObjectManager();

        $storeManager = $di->get('Magento\Store\Model\StoreManagerInterface');
        $sequenceCreator = $di->get('Magento\SalesSequence\Observer\SequenceCreatorObserver');

        $stores = [$storeManager->getStore($storeCode)];
        if (!$storeCode) {
            $stores = $storeManager->getStores();
        }

        /** @var \Magento\Store\Api\StoreInterface $store */
        foreach ($stores as $store) {
            $output->writeln(sprintf('<info>Updating sequence for store <comment>%s</comment> (<comment>%s #%d</comment>)</info>', $store->getName(), $store->getCode(), $store->getId()));

            $event = new Event(['store' => $store]);
            $event->setName('store_add');

            $observer = new Observer([
                'store' => $store,
                'event' => $event
            ]);

            $sequenceCreator->execute($observer);
        }

        return self::SUCCESS;
    }
}
