<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Setup;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateModulesSequenceCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('setup:update-modules-sequence')
            ->setDescription('Update modules sequence in app/etc/config.php')
            ->addOption(
                'keep-generated',
                '',
                InputOption::VALUE_NONE,
                'Cleanup generated classes and view files and reset ObjectManager'
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
        $this->detectMagento($output);

        $configuration = require BP . '/setup/config/application.config.php';
        $bootstrapApplication = new \Magento\Setup\Application();
        $application = $bootstrapApplication->bootstrap($configuration);

        $serviceManager = $application->getServiceManager();

        $objectManager = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER)->getObjectManager();

        $omProvider = $serviceManager->get(\Magento\Setup\Model\ObjectManagerProvider::class);
        $omProvider->setObjectManager($objectManager);

        $objectManager->create(\Magento\Setup\Model\Installer::class);

        $coreInstaller = $objectManager->create();
        $keepGeneratedFiles = $input->getOption('keep-generated');
        $output->writeln(
            sprintf(
                '<info>Update modules sequence.</info> <comment>keep-generated=%s</comment>',
                $keepGeneratedFiles ? 'yes' : 'no'
            )
        );
        $coreInstaller->updateModulesSequence($keepGeneratedFiles);

        return Command::SUCCESS;
    }
}
