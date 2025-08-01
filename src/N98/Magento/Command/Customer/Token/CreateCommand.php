<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Customer\Token;

use Exception;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use N98\Magento\Command\Customer\AbstractCustomerCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 * @package N98\Magento\Command\Customer\Token
 */
class CreateCommand extends AbstractCustomerCommand
{
    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    protected function configure()
    {
        $this
            ->setName('customer:token:create')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website of the customer')
            ->addOption('no-newline', null, InputOption::VALUE_NONE, 'do not output the trailing newline')
            ->setDescription('Create a new token for a customer.');
    }

    /**
     * @param Token $tokenModelFactory
     */
    public function inject(TokenFactory $tokenModelFactory)
    {
        $this->tokenModelFactory = $tokenModelFactory;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect and Init Magento
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        // Detect customer
        $customer = $this->detectCustomer($input, $output);
        if ($customer === null) {
            return Command::FAILURE;
        }

        /** @var Token $tokenModel */
        $tokenModel = $this->tokenModelFactory->create();
        $tokenModel->createCustomerToken($customer->getId());

        $output->write($tokenModel->getToken(), !$input->getOption('no-newline'));

        return Command::SUCCESS;
    }
}
