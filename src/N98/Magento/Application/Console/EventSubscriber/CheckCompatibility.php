<?php

namespace N98\Magento\Application\Console\EventSubscriber;

use Exception;
use Magento\Framework\App\DistributionMetadataInterface;
use Magento\Framework\App\ProductMetadataInterface;
use N98\Magento\Application;
use N98\Magento\Application\ApplicationAwareInterface;
use N98\Magento\Application\Console\Events;
use N98\Magento\Command\Installer\InstallCommand;
use N98\Magento\Command\MagentoCoreProxyCommand;
use N98\Magento\Command\SelfUpdateCommand;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckCompatibility
 * @package N98\Magento\Application\Console\EventSubscriber
 */
class CheckCompatibility implements EventSubscriberInterface, ApplicationAwareInterface
{
    /**
     * @var Application
     */
    private $application;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::RUN_BEFORE => 'checkCompatibility',
        ];
    }

    /**
     * Display a warning if a running n98-magerun as root user
     *
     * @param ConsoleEvent $event
     * @return void
     */
    public function checkCompatibility(ConsoleEvent $event)
    {
        if ($event->getInput()->hasParameterOption('--skip-magento-compatibility-check')) {
            // early exit if we should skip the compatibility check
            return;
        }

        $commandName = $event->getInput()->getFirstArgument();
        if ($commandName === null) {
            return;
        }
        try {
            $command = $this->application->get($commandName);
        } catch (CommandNotFoundException $e) {
            // let symfony handle this
            return;
        }

        if ($command instanceof MagentoCoreProxyCommand) {
            // We do no compatibility check for Magento Core Commands
            return;
        }

        if ($command instanceof InstallCommand) {
            // We do not check the installer command
            return;
        }

        if ($command instanceof SelfUpdateCommand) {
            // We do not check the update command
            return;
        }

        try {
            $this->application->initMagento(true);
            $objectManager = $this->application->getObjectManager();
            if (!$objectManager) {
                return;
            }

            $productMetadata = $objectManager->get(ProductMetadataInterface::class);
            $currentMagentoVersion = $productMetadata->getVersion();

            // We cannot check if no version is defined
            if (!$this->isStableVersion($currentMagentoVersion)) {
                return;
            }

            if ($productMetadata instanceof DistributionMetadataInterface
                && $productMetadata->getDistributionName() === 'Mage-OS'
            ) {
                $this->checkMageOsDistribution($currentMagentoVersion, $event);
            }

            if ($productMetadata->getName() === 'Magento') {
                $this->checkMagentoDistribution($currentMagentoVersion, $event);
            }
        } catch (Exception $e) {
            //
        }
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @param string $currentMagentoVersion
     * @return bool
     */
    private function isStableVersion(string $currentMagentoVersion): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $currentMagentoVersion);
    }

    /**
     * Check if the current Mage-OS version is compatible with the current n98-magerun2 version
     *
     * @param $currentMagentoVersion
     * @param ConsoleEvent $event
     * @return void
     */
    protected function checkMageOsDistribution($currentMagentoVersion, ConsoleEvent $event): void
    {
        // currently there is no incompatible version available
    }

    /**
     * @param $currentMagentoVersion
     * @param ConsoleEvent $event
     * @return void
     */
    protected function checkMagentoDistribution($currentMagentoVersion, ConsoleEvent $event): void
    {
        if (version_compare($currentMagentoVersion, '2.3.0', '<')) {
            $output = $event->getOutput();
            $output->writeln([
                '',
                '<error>You are running an incompatible version of n98-magerun2!</error>',
                '<error>Your shop version has to be >2.3.0</error>',
                '',
                '',
                '<comment>Current Magento Version     : ' . $currentMagentoVersion . '</comment>',
                '<comment>Current n98-magerun2 Version: ' . $this->application->getVersion() . '</comment>',
                '',
                '',
                '<info>Please download an older version of n98-magerun2.</info>',
                '',
                '<info>Visit: https://files.magerun.net/old_versions.php</info>',
                '',
                '    Magento 2.2.x => n98-magerun2 v3.2.0',
                '    Magento 2.1.x => n98-magerun2 v3.2.0',
                '    Magento 2.0.x => n98-magerun2 v2.3.3',
                '',
                '',
            ]);

            exit(1);
        }
    }
}
