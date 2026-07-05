<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin\User;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteUserCommand
 */
class DeleteUserCommand extends AbstractAdminUserCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('admin:user:delete')
            ->addArgument('id', InputArgument::OPTIONAL, 'Username or Email')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
            ->setDescription('Delete the account of a adminhtml user.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        // Username
        $id = $input->getArgument('id');
        if ($id === null) {
            $id = text(
                '<question>Username or Email:</question>',
                validate: fn ($value) => $value === '' ? 'Please enter a username or email' : null
            );
        }

        $user = $this->userModel->loadByUsername($id);
        if (!$user->getId()) {
            $user = $this->userModel->load($id, 'email');
        }

        if (!$user->getId()) {
            $output->writeln('<error>User was not found</error>');
            return Command::FAILURE;
        }

        $shouldRemove = $input->getOption('force');
        if (!$shouldRemove) {
            $shouldRemove = confirm('<question>Are you sure?</question>', default: false);
        }

        if ($shouldRemove) {
            try {
                $user->delete();
                $output->writeln('<info>User was successfully deleted</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                return Command::FAILURE;
            }
        } else {
            $output->writeln('<error>Aborting delete</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
