<?php

namespace N98\Magento\Command\Admin\User;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:list')
            ->setDescription('List admin users.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->addOption(
                'sort',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sort user list by a field (e.g., user_id, username, email, logdate)'
            );
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

        $userCollection = $this->userModel->getCollection();

        $sortField = $input->getOption('sort') ?: 'user_id';
        $userCollection->setOrder($sortField, 'ASC');

        $table = [];
        foreach ($userCollection as $user) {
            $table[] = [
                $user->getId(),
                $user->getUsername(),
                $user->getEmail(),
                $user->getIsActive() ? 'active' : 'inactive',
                $user->getLogdate(),
            ];
        }
        $this->getHelper('table')
            ->setHeaders(['id', 'username', 'email', 'status', 'logdate'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
