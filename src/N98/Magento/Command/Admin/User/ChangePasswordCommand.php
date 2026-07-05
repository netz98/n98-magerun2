<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin\User;

use Exception;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChangePasswordCommand
 * @package N98\Magento\Command\Admin\User
 */
class ChangePasswordCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:change-password')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->setDescription('Changes the password of a adminhtml user.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        // Username
        $username = $username = $input->getArgument('username');
        if ($username === null) {
            $username = text(
                '<question>Username:</question>',
                validate: fn ($value) => trim($value) === '' ? 'Please enter a valid username' : null
            );
        }

        $user = $this->userModel->loadByUsername($username);
        if ($user->getId() <= 0) {
            $output->writeln('<error>User was not found</error>');
            return Command::SUCCESS;
        }

        // Password
        $password = $input->getArgument('password');
        if ($password === null) {
            $password = password('<question>Password:</question>');
        }

        try {
            // @see \Magento\Framework\Session\SessionManager::isSessionExists Hack to prevent session problems
            @session_start();

            $result = $user->validate();
            if (is_array($result)) {
                throw new Exception(implode(PHP_EOL, $result));
            }
            $user->setPassword($password);
            $user->setForceNewPassword(true);
            $this->userResource->save($user);
            $this->userResource->trackPassword($user, $user->getPassword());
            $output->writeln('<info>Password successfully changed</info>');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
