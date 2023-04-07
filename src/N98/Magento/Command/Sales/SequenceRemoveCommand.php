<?php

namespace N98\Magento\Command\Sales;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SequenceRemoveCommand extends AbstractMagentoCommand
{

    protected function configure()
    {
        $this->setName('sales:sequence:remove')
            ->setDescription('Remove sequence tables and metadata for given store or all stores')
            ->addArgument('store', InputArgument::OPTIONAL, 'Store code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $storeCode = $input->getArgument('store');
        $interaction = ! $input->getOption('no-interaction');

        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');

        if ($interaction) {
            $question = new ConfirmationQuestion(
                '<error>Only continue if you know what you\'re doing!</error> ' .
                '<question>Do you know what you are doing?</question> <comment>[n]</comment>: ',
                false
            );
            $shouldContinue = $questionHelper->ask(
                $input,
                $output,
                $question
            );

            if (! $shouldContinue) {
                return self::INVALID;
            }
        }

        $di = $this->getObjectManager();

        $storeManager = $di->get('Magento\Store\Model\StoreManagerInterface');
        $sequenceRemoval = $di->get('Magento\SalesSequence\Observer\SequenceRemovalObserver');

        $stores = [$storeManager->getStore($storeCode)];
        if (!$storeCode) {
            $stores = $storeManager->getStores();
        }

        /** @var \Magento\Store\Api\StoreInterface $store */
        foreach ($stores as $store) {

            if ($interaction) {
                $question = new ConfirmationQuestion(
                    sprintf(
                        '<question>Are you sure to remove sequence for ' .
                            '<comment>%s (%s #%d)</comment>?</question> ' .
                            '<comment>[n]</comment>: ',
                        $store->getName(),
                        $store->getCode(),
                        $store->getId()
                    ),
                    false
                );
                $shouldRemove = $questionHelper->ask(
                    $input,
                    $output,
                    $question
                );

                if (! $shouldRemove) {
                    continue;
                }
            }

            $output->writeln(sprintf('<info>Removing sequence for store <comment>%s</comment> (<comment>%s #%d</comment>)</info>', $store->getName(), $store->getCode(), $store->getId()));

            $event = new Event(['store' => $store]);
            $event->setName('store_remove');

            $observer = new Observer([
                'store' => $store,
                'event' => $event
            ]);

            $sequenceRemoval->execute($observer);
        }

        return self::SUCCESS;
    }
}
