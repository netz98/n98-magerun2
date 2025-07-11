<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System;

use Magento\Framework\App\MaintenanceMode;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MaintenanceCommand
 * @package N98\Magento\Command\System
 */
class MaintenanceCommand extends AbstractMagentoCommand
{
    const ALREADY_DISABLED_MESSAGE = 'Nothing to disable, maintenance mode is not enabled!';
    const ALREADY_ENABLED_MESSAGE = 'Maintenance mode is already enabled.';
    const ENABLED_MESSAGE = 'Maintenance mode <info>on</info>';
    const DISABLED_MESSAGE = 'Maintenance mode <info>off</info>';
    const WROTE_IP_MESSAGE = 'Wrote IP exclusion file.';
    const DELETED_IP_MESSAGE = 'Deleted IP exclusion file.';

    protected function configure()
    {
        $this
            ->setName('sys:maintenance')
            ->addOption(
                'on',
                null,
                InputOption::VALUE_NONE,
                'Set to [1] to enable maintenance mode. Optionally supply a comma separated list of IP addresses ' .
                'to exclude from being affected'
            )
            ->addOption(
                'off',
                null,
                InputOption::VALUE_NONE,
                'Set to [1] to disable maintenance mode. Set to [d] to also delete the list with excluded IP addresses.'
            )
            ->setDescription('Toggles maintenance mode if --on or --off preferences are not set');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        /* @var $flagDir \Magento\Framework\Filesystem\Directory\Write */
        $flagDir = $this->getObjectManager()
                        ->get('\Magento\Framework\Filesystem')
                        ->getDirectoryWrite(MaintenanceMode::FLAG_DIR);

        if ($input->getOption('off')) {
            if (!$flagDir->isExist(MaintenanceMode::FLAG_FILENAME)) {
                $output->writeln(self::ALREADY_DISABLED_MESSAGE);

                return Command::SUCCESS;
            }

            $this->handleDisable($flagDir, $output, $input->getOption('off'));

            return Command::SUCCESS;
        }

        if ($input->getOption('on')) {
            if ($flagDir->isExist(MaintenanceMode::FLAG_FILENAME)) {
                $output->writeln(self::ALREADY_ENABLED_MESSAGE);

                return Command::SUCCESS;
            }

            $this->handleEnable($flagDir, $output, $input->getOption('on'));
            return Command::SUCCESS;
        }

        // Toggle based on existence of flag file
        if ($flagDir->isExist(MaintenanceMode::FLAG_FILENAME)) {
            $this->handleDisable($flagDir, $output);
        } else {
            $this->handleEnable($flagDir, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * @param \Magento\Framework\Filesystem\Directory\Write $flagDir
     * @param OutputInterface $output
     * @param null $offOption
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function handleDisable(
        \Magento\Framework\Filesystem\Directory\Write $flagDir,
        OutputInterface $output,
        $offOption = null
    ) {
        $flagDir->delete(MaintenanceMode::FLAG_FILENAME);
        $output->writeln(self::DISABLED_MESSAGE);

        if ($offOption === 'd') {
            // Also delete IP flag file
            $flagDir->delete(MaintenanceMode::IP_FILENAME);
            $output->writeln(self::DELETED_IP_MESSAGE);
        }
    }

    /**
     * @param \Magento\Framework\Filesystem\Directory\Write $flagDir
     * @param OutputInterface $output
     * @param null $onOption
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function handleEnable(
        \Magento\Framework\Filesystem\Directory\Write $flagDir,
        OutputInterface $output,
        $onOption = null
    ) {
        $flagDir->touch(MaintenanceMode::FLAG_FILENAME);
        $output->writeln(self::ENABLED_MESSAGE);

        if ($onOption !== null) {
            // Write IPs to exclusion file
            $flagDir->writeFile(MaintenanceMode::IP_FILENAME, $onOption);
            $output->writeln(self::WROTE_IP_MESSAGE);
        }
    }
}
