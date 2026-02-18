<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Composer;

use Composer\Composer;
use Composer\IO\NullIO;
use MagentoHackathon\Composer\Magento\Deploy\Manager\Entry;
use MagentoHackathon\Composer\Magento\DeployManager;
use MagentoHackathon\Composer\Magento\Installer;
use MagentoHackathon\Composer\Magento\Plugin;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedeployBasePackagesCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('composer:redeploy-base-packages');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->detectMagento($output)) {
            $output->writeln('<error>No Magento installation found</error>');

            return Command::FAILURE;
        }

        $this->initMagento();

        $commandConfig = $this->getCommandConfig();

        $composer = MagentoComposer::initBundledComposer($this->getApplication()->getMagentoRootFolder());

        $magentoPlugin = new Plugin();
        $composer->getPluginManager()->addPlugin($magentoPlugin);

        /** @var $installer Installer */
        $installer = $composer->getInstallationManager()->getInstaller('magento2-module');

        $deployManager = new DeployManager(new NullIO());

        foreach ($commandConfig['packages'] as $basePackageName) {
            $this->redeployPackage($composer, $deployManager, $installer, $output, $basePackageName);
        }

        $deployManager->doDeploy();

        $magentoPlugin->onNewCodeEvent(
            new \Composer\Script\Event(
                'post-package-install',
                $composer,
                new NullIO()
            )
        );

        return Command::SUCCESS;
    }

    protected function redeployPackage(
        Composer $composer,
        DeployManager $deployManager,
        Installer $installer,
        OutputInterface $output,
        string $packageName
    ): void {
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $composerPackage = $localRepo->findPackage($packageName, '*');

        if ($composerPackage === null) {
            return;
        }

        $output->writeln(sprintf('<info>Redeploy package: </info><comment>%s</comment>', $packageName));

        $extra = $composer->getPackage()->getExtra();

        $ignoredFilesList = [];
        if (isset($extra['magento-deploy-ignore']['*'])) {
            $ignoredFilesList = $extra['magento-deploy-ignore']['*'];
        }

        $mappings = $installer->getParser($composerPackage)->getMappings();
        $strategy = $installer->getDeployStrategy($composerPackage);
        $strategy->setMappings($mappings);

        $deployManagerEntry = new Entry();
        $deployManagerEntry->setPackageName($packageName);
        $deployManagerEntry->setDeployStrategy($strategy);
        $deployManager->addPackage($deployManagerEntry);

        foreach ($mappings as $mapping) {
            if (!in_array('/' . current($mapping), $ignoredFilesList, true)) {
                $output->writeln(sprintf('<comment>%s</comment>', current($mapping)));
            }
        }
    }
}
