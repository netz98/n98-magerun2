<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Admin\User;

use function Laravel\Prompts\select;
use Magento\User\Model\ResourceModel\User as UserResourceModel;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\User;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ToggleBlockCommand
 * @package N98\Magento\Command\Admin\User
 */
class ChangeStatusCommand extends AbstractMagentoCommand
{
    protected const USER_ARGUMENT = 'user';
    protected const ACTIVATE_OPTION = 'activate';
    protected const DEACTIVATE_OPTION = 'deactivate';

    protected $userResourceModel;
    protected $userCollectionFactory;
    protected $userModel;

    public function inject(
        UserResourceModel $userResourceModel,
        CollectionFactory $userCollectionFactory,
        User $userModel
    ): void {
        $this->userResourceModel = $userResourceModel;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->userModel = $userModel;
    }

    protected function configure(): void
    {
        $this
            ->setName('admin:user:change-status')
            ->addArgument(self::USER_ARGUMENT, InputArgument::OPTIONAL, 'Username or email for the admin user')
            ->addOption(self::ACTIVATE_OPTION, null, InputOption::VALUE_NONE, 'Activate the user')
            ->addOption(self::DEACTIVATE_OPTION, null, InputOption::VALUE_NONE, 'Deactivate the user')
            ->setDescription(
                'Set the status of an admin user, if no status is given the status will be toggled. 
                Note: the first found user is edited (since it is possible to use someone else\'s email as your 
                username, although you should try to avoid this scenario).'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $username = $input->getArgument(self::USER_ARGUMENT);
        if (!\is_string($username) || $username === '') {
            if (!$input->isInteractive()) {
                $output->writeln(
                    'Please provide an username or email for the admin user. Use --help for more information.'
                );

                return Command::FAILURE;
            }

            $username = $this->selectUsername();
            if ($username === null) {
                $output->writeln(\sprintf('<info>%s</info>', $this->getNoUsersMessage()));

                return Command::SUCCESS;
            }
        }

        $user = $this->getUser($username);
        if ($user === null) {
            $output->writeln(\sprintf('Could not find a user associated to <info>%s</info>.', $username));

            return Command::FAILURE;
        }

        $newStatus = $this->getNewStatusForUser($user, $input);
        $user->setIsActive($newStatus);
        $this->userResourceModel->save($user);
        $output->writeln(\sprintf('User has been <info>%s</info>.', $newStatus ? 'activated' : 'deactivated'));

        return Command::SUCCESS;
    }

    protected function getUser(string $username): ?User
    {
        $collection = $this->userCollectionFactory->create();
        // Get the user where either the username or the email matches.
        $collection->addFieldToFilter(
            [
                'username',
                'email',
            ],
            [
                $username,
                $username,
            ]
        );
        $collection->getItems();
        $userId = $collection->getFirstItem()->getUserId();
        if ($userId === null) {
            return null;
        }

        // Load the user through the resource model instead of using the collection item directly, so that
        // the load lifecycle (e.g. Magento\User\Model\ResourceModel\User::_afterLoad()) deserializes
        // "extra" into an array. Otherwise, saving the collection item would re-encode "extra" as JSON.
        $this->userResourceModel->load($this->userModel, $userId);

        return $this->userModel->getUserId() !== null ? $this->userModel : null;
    }

    protected function getNewStatusForUser(User $user, InputInterface $input): bool
    {
        if ($input->getOption(self::ACTIVATE_OPTION) === true) {
            return true;
        }

        if ($input->getOption(self::DEACTIVATE_OPTION) === true) {
            return false;
        }

        // If no option is supplied, toggle the status.
        return !$user->getIsActive();
    }

    protected function selectUsername(): ?string
    {
        $options = [];
        foreach ($this->userCollectionFactory->create()->getItems() as $user) {
            if (!$this->isSelectableUser($user)) {
                continue;
            }

            $options[(string) $user->getUsername()] = \sprintf(
                '%s (%s) - %s',
                $user->getUsername(),
                $user->getEmail(),
                $user->getIsActive() ? 'active' : 'inactive'
            );
        }

        if ($options === []) {
            return null;
        }

        return (string) select($this->getSelectQuestion(), $options);
    }

    protected function isSelectableUser(User $user): bool
    {
        return true;
    }

    protected function getSelectQuestion(): string
    {
        return '<question>Select an admin user:</question>';
    }

    protected function getNoUsersMessage(): string
    {
        return 'No admin users found.';
    }
}
