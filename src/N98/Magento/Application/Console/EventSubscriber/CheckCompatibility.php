<?php

namespace N98\Magento\Application\Console\EventSubscriber;

use N98\Magento\Application;
use N98\Magento\Application\ApplicationAwareInterface;
use N98\Magento\Application\Console\Event;
use N98\Magento\Application\Console\Events;
use Symfony\Component\Console\Event\ConsoleEvent;
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
        try {
            $objectManager = $this->application->getObjectManager();
            if (!$objectManager) {
                return;
            }

            $productMetadata = $objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);

            $currentMagentoVersion = $productMetadata->getVersion();
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
        } catch (\Exception $e) {
            //
        }
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }
}
