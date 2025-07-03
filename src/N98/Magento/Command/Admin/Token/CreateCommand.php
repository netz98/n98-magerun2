<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin\Token;

use Exception;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\User\Model\User;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
            ->addOption('no-newline', null, InputOption::VALUE_NONE, 'do not output the trailing newline')
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

        // Username
        $username = $input->getArgument('username');
        if ($username === null) {
            /** @var $questionHelper QuestionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question('<question>Username:</question>');
            $question->setValidator(function ($value) {
                if ($value === '') {
                    throw new \Exception('Please enter a valid username');
                }

                return $value;
            });
            $username = $questionHelper->ask($input, $output, $question);
        }

        $adminUser = $this->userModel->loadByUsername($username);
        if ($adminUser->getId() <= 0) {
            $output->writeln('<error>User was not found</error>');
            return Command::FAILURE;
        }

        /** @var Token $tokenModel */
        $tokenModel = $this->tokenModelFactory->create();
        $tokenModel->createAdminToken($adminUser->getId());

        $output->write($tokenModel->getToken(), !$input->getOption('no-newline'));

        return Command::SUCCESS;
    }
}
