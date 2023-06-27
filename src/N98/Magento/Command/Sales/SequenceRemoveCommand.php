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
        $help = <<<'HELP'
                The 'sales:sequence:remove' command removes sequence tables and metadata 
                for a specified store or for all stores if no store is specifically mentioned.
                The store argument refers to the specific store for which the sequence tables 
                and metadata are to be removed. This can be specified by either the store code or the store ID. 
                If no store is specified, the command will apply to all stores.
                
                <comment>Examples</comment>:
                
                - Removes sequence tables and metadata for the store with ID 5.
                  <info>sales:sequence:remove 5</info>         
                
                - Removes sequence tables and metadata for the store with code 'store_en'. 
                  <info>sales:sequence:remove 'store_en'</info>
                HELP;

        $this
            ->setName('sales:sequence:remove')
            ->setDescription('Remove sequence tables and metadata for given store or all stores')
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
