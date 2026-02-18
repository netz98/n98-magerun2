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

        $this->initializeMagento($output);

        $commandConfig = $this->getCommandConfig();
        $composer = $this->createBundledComposer();
        $magentoPlugin = $this->registerMagentoPlugin($composer);
        $installer = $this->getMagentoInstaller($composer);
        $deployManager = new DeployManager(new NullIO());

        foreach ($commandConfig['packages'] as $basePackageName) {
            $this->redeployPackage($composer, $deployManager, $installer, $output, $basePackageName);
        }

        $deployManager->doDeploy();
        $this->triggerPostPackageInstallEvent($magentoPlugin, $composer);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    private function initializeMagento(OutputInterface $output): void
    {
        try {
            $this->initMagento();
        } catch (\Exception $e) {
            $output->writeln('<warning>Magento initialization failed, using bundled composer</warning>');
        }
    }

    /**
     * @return \Composer\Composer
     * @throws \Composer\Json\JsonValidationException
     */
    private function createBundledComposer(): Composer
    {
        return MagentoComposer::initBundledComposer($this->getApplication()->getMagentoRootFolder());
    }

    /**
     * @param \Composer\Composer $composer
     * @return \MagentoHackathon\Composer\Magento\Plugin
     * @throws \ReflectionException
     */
    private function registerMagentoPlugin(Composer $composer): Plugin
    {
        $magentoPlugin = new Plugin();
        $pluginManager = $composer->getPluginManager();
        $addPluginMethod = new \ReflectionMethod($pluginManager, 'addPlugin');
        $addPluginArguments = $this->buildAddPluginArguments($addPluginMethod, $composer, $magentoPlugin);

        $addPluginMethod->invokeArgs($pluginManager, $addPluginArguments);

        return $magentoPlugin;
    }

    /**
     * @param \ReflectionMethod $addPluginMethod
     * @param \Composer\Composer $composer
     * @param \MagentoHackathon\Composer\Magento\Plugin $magentoPlugin
     * @return \MagentoHackathon\Composer\Magento\Plugin[]
     */
    private function buildAddPluginArguments(
        \ReflectionMethod $addPluginMethod,
        Composer $composer,
        Plugin $magentoPlugin
    ): array {
        $addPluginArguments = [$magentoPlugin];
        $addPluginParameters = $addPluginMethod->getParameters();

        if (
            isset($addPluginParameters[1], $addPluginParameters[2])
            && $addPluginParameters[1]->getName() === 'isGlobalPlugin'
            && $addPluginParameters[2]->getName() === 'sourcePackage'
        ) {
            $addPluginArguments[] = false;
            $addPluginArguments[] = $composer->getPackage();

            return $addPluginArguments;
        }

        if (isset($addPluginParameters[1]) && $addPluginParameters[1]->getName() === 'sourcePackage') {
            $addPluginArguments[] = $composer->getPackage();
        }

        return $addPluginArguments;
    }

    /**
     * @param \Composer\Composer $composer
     * @return \MagentoHackathon\Composer\Magento\Installer
     */
    private function getMagentoInstaller(Composer $composer): Installer
    {
        $installer = $composer->getInstallationManager()->getInstaller('magento2-module');
        if (!$installer instanceof Installer) {
            throw new \RuntimeException('Magento2 module installer is not available');
        }

        return $installer;
    }

    /**
     * @param \MagentoHackathon\Composer\Magento\Plugin $magentoPlugin
     * @param \Composer\Composer $composer
     * @return void
     */
    private function triggerPostPackageInstallEvent(Plugin $magentoPlugin, Composer $composer): void
    {
        $magentoPlugin->onNewCodeEvent(
            new \Composer\Script\Event(
                'post-package-install',
                $composer,
                new NullIO()
            )
        );
    }

    /**
     * @param \Composer\Composer $composer
     * @param \MagentoHackathon\Composer\Magento\DeployManager $deployManager
     * @param \MagentoHackathon\Composer\Magento\Installer $installer
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $packageName
     * @return void
     * @throws \ErrorException
     */
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

        $ignoredFilesList = $this->getIgnoredFilesList($composer);

        $mappings = $installer->getParser($composerPackage)->getMappings();
        $strategy = $installer->getDeployStrategy($composerPackage);
        $strategy->setMappings($mappings);

        $deployManagerEntry = new Entry();
        $deployManagerEntry->setPackageName($packageName);
        $deployManagerEntry->setDeployStrategy($strategy);
        $deployManager->addPackage($deployManagerEntry);

        $this->printPackageMappings($mappings, $ignoredFilesList, $output);
    }

    private function getIgnoredFilesList(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();
        if (!isset($extra['magento-deploy-ignore']['*'])) {
            return [];
        }

        return $extra['magento-deploy-ignore']['*'];
    }

    private function printPackageMappings(array $mappings, array $ignoredFilesList, OutputInterface $output): void
    {
        foreach ($mappings as $mapping) {
            if (!in_array('/' . current($mapping), $ignoredFilesList, true)) {
                $output->writeln(sprintf('<comment>%s</comment>', current($mapping)));
            }
        }
    }
}
