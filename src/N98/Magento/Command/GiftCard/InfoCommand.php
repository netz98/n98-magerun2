<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\GiftCard;

use Magento\GiftCardAccount\Model\Giftcardaccount;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InfoCommand
 * @package N98\Magento\Command\GiftCard
 */
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
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $this->setAdminArea();

        $card = $this->getGiftcard($input->getArgument('code'));
        if (!$card->getId()) {
            $output->writeln('<error>No gift card found for that code</error>');
            return Command::FAILURE;
        }

        $data = [
            ['Gift Card Account ID', $card->getId()],
            ['Code', $card->getCode()],
            // Giftcardaccount is part of Adobe Commerce -> no completion here
            // @phpstan-ignore-next-line
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

        return Command::SUCCESS;
    }
}
