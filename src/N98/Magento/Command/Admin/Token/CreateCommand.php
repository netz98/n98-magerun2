<?php

namespace N98\Magento\Command\Admin\Token;

use Exception;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\User\Model\User;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 * @package N98\Magento\Command\Admin\Token
 */
class CreateCommand extends AbstractMagentoCommand
{
    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @var User
     */
    protected $userModel;

    protected function configure()
    {
        $this
            ->setName('admin:token:create')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->setDescription('Create a new token for an admin user.');
    }

    /**
     * @param Token $tokenModelFactory
     * @param User $userModel $userModel
     */
    public function inject(
        TokenFactory $tokenModelFactory,
        User $userModel
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userModel = $userModel;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect and Init Magento
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        /** @var $dialog DialogHelper */
        $dialog = $this->getHelper('dialog');

        // Username
        if (($username = $input->getArgument('username')) == null) {
            $username = $dialog->ask($output, '<question>Username:</question>');
        }

        $adminUser = $this->userModel->loadByUsername($username);
        if ($adminUser->getId() <= 0) {
            $output->writeln('<error>User was not found</error>');
            return;
        }

        /** @var Token $tokenModel */
        $tokenModel = $this->tokenModelFactory->create();
        $tokenModel->createAdminToken($adminUser->getId());

        $output->write($tokenModel->getToken());
    }
}
