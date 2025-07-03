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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveCommand
 * @package N98\Magento\Command\GiftCard
 */
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $this->setAdminArea();

        $code = $input->getArgument('code');
        $card = $this->getGiftcard($code);

        if (!$card->getId()) {
            $output->writeln('<info>No gift card with matching code found</info>');
            return Command::FAILURE;
        }

        $card->delete();

        $output->writeln('<info>Deleted gift card with code <comment>' . $code . '</comment></info>');

        return Command::SUCCESS;
    }
}
