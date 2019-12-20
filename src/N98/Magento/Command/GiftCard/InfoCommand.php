<?php

namespace N98\Magento\Command\GiftCard;

use Magento\GiftCardAccount\Model\Giftcardaccount;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends AbstractGiftCardCommand
{
    /**
     * Setup
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('giftcard:info')
            ->addArgument('code', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Gift card code')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Get gift card account information by code');

        $help = <<<HELP
Get gift card account information by code
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $this->setAdminArea();

        $card = $this->getGiftcard($input->getArgument('code'));
        if (!$card->getId()) {
            $output->writeln('<error>No gift card found for that code</error>');
            return;
        }

        $data = [
            ['Gift Card Account ID', $card->getId()],
            ['Code', $card->getCode()],
            ['Status', Giftcardaccount::STATUS_ENABLED == $card->getStatus() ? 'Enabled' : 'Disabled'],
            ['Date Created', $card->getDateCreated()],
            ['Expiration Date', $card->getDateExpires()],
            ['Website ID', $card->getWebsiteId()],
            ['Remaining Balance', $card->getBalance()],
            ['State', $card->getStateText()],
            ['Is Redeemable', $card->getIsRedeemable()],
        ];

        $this->getHelper('table')
            ->setHeaders(['Name', 'Value'])
            ->setRows($data)
            ->renderByFormat($output, $data, $input->getOption('format'));
    }
}
