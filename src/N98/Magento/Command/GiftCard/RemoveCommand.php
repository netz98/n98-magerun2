<?php

namespace N98\Magento\Command\GiftCard;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemoveCommand extends AbstractGiftCardCommand
{
    /**
     * Setup
     * 
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('giftcard:remove')
            ->addArgument('code', InputArgument::REQUIRED, 'Gift card code')
            ->setDescription('Remove a gift card account by code');

        $help = <<<HELP
Remove a gift card account by code
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

        $code = $input->getArgument('code');
        $card = $this->getGiftcard($code);

        if (!$card->getId()) {
            $output->writeln('<info>No gift card with matching code found</info>');
            return;
        }

        $card->delete();

        $output->writeln('<info>Deleted gift card with code <comment>' . $code . '</comment></info>');
    }
}
