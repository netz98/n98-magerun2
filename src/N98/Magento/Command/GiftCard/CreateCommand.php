<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\GiftCard;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 * @package N98\Magento\Command\GiftCard
 */
class CreateCommand extends AbstractGiftCardCommand
{
    /**
     * Setup
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('giftcard:create')
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount for new gift card')
            ->addOption('website', null, InputOption::VALUE_OPTIONAL, 'Website ID to attach gift card to')
            ->addOption('expires', null, InputOption::VALUE_OPTIONAL, 'Expiration date in YYYY-MM-DD format')
            ->setDescription('Create a new gift card with a specified amount');

        $help = <<<HELP
Create a new gift card with a specified amount
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
            return;
        }

        $this->setAdminArea();

        $giftcard = $this->getGiftcard();
        $giftcard->setData(
            [
                'status'        => 1,
                'is_redeemable' => 1,
                'website_id'    => $input->getOption('website')
                    ?: $this->getObjectManager()->get('Magento\Store\Model\StoreManager')->getWebsite(true)->getId(),
                'balance'      => $input->getArgument('amount'),
                'date_expires' => $input->getOption('expires'),
            ]
        );

        $giftcard->save();
        if (!$giftcard->getId()) {
            $output->writeln('<error>Failed to create gift card</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Gift card <comment>' . $giftcard->getCode() . '</comment> was created</info>');

        return Command::SUCCESS;
    }
}
