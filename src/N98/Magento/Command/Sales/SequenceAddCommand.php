<?php

namespace N98\Magento\Command\Sales;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\SalesSequence\Observer\SequenceCreatorObserver;
use Magento\Store\Api\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SequenceAddCommand extends AbstractMagentoCommand
{

    protected function configure()
    {
        $help = <<<'HELP'
                The 'sales:sequence:add' command adds sequence tables and metadata 
                for a specified store or for all stores if no store is specifically mentioned.
                The store argument refers to the specific store for which the sequence tables 
                and metadata are to be added. This can be specified by either the store code or the store ID. 
                If no store is specified, the command will apply to all stores.
                
                <comment>Examples</comment>:
                
                - Adds sequence tables and metadata for the store with ID 5.
                  <info>sales:sequence:add 5</info>         
                
                - Adds sequence tables and metadata for the store with code 'store_en'. 
                  <info>sales:sequence:add 'store_en'</info>
                HELP;

        $this->setName('sales:sequence:add')
            ->setDescription('Add the sequence tables and metadata for given store or all stores')
            ->setHelp($help)
            ->addArgument('store', InputArgument::OPTIONAL, 'The store code or ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $storeCode = $input->getArgument('store');

        $di = $this->getObjectManager();

        $storeManager = $di->get(StoreManagerInterface::class);
        $sequenceCreator = $di->get(SequenceCreatorObserver::class);

        $stores = [$storeManager->getStore($storeCode)];
        if (!$storeCode) {
            $stores = $storeManager->getStores();
        }

        /** @var StoreInterface $store */
        foreach ($stores as $store) {
            $output->writeln(
                sprintf(
                    '<info>Updating sequence for store <comment>%s</comment> (<comment>%s #%d</comment>)</info>',
                    $store->getName(),
                    $store->getCode(),
                    $store->getId()
                )
            );

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
