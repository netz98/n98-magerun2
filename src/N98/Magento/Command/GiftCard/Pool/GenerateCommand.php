<?php

namespace N98\Magento\Command\GiftCard\Pool;

use N98\Magento\Command\GiftCard\AbstractGiftCardCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\GiftCardAccount\Model\Pool;

class GenerateCommand extends AbstractGiftCardCommand
{
    /**
     * Setup
     * 
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('giftcard:pool:generate')
            ->setDescription('Generate a new gift card pool');

        $help = <<<HELP
Generate a new gift card pool
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $this->setAdminArea();

        try {
            $this
                ->getObjectManager()
                ->create(Pool::class)
                ->generatePool();

            $output->writeln('<info>Gift card pool was generated.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to generate gift card pool!</error>');
        }
    }
}
