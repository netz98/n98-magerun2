<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * (c) netz98 GmbH <info@netz98.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace N98\Magento\Command\Admin\User;

use Magento\User\Model\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class ActivateCommand extends ChangeStatusCommand
{
    protected function configure(): void
    {
        $this
            ->setName('admin:user:activate')
            ->setAliases(['admin:user:enable'])
            ->addArgument(self::USER_ARGUMENT, InputArgument::REQUIRED, 'Username or email for the admin user')
            ->setDescription('Activates an admin user.');
    }

    protected function getNewStatusForUser(User $user, InputInterface $input): bool
    {
        return true;
    }
}
