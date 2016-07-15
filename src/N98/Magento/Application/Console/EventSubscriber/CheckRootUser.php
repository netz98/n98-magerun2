<?php

namespace N98\Magento\Application\Console\EventSubscriber;

use N98\Magento\Application\Console\Event;
use N98\Magento\Application\Console\Events;
use N98\Magento\Application\OptionParser;
use N98\Util\OperatingSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckRootUser implements EventSubscriberInterface
{
    const WARNING_ROOT_USER = "<error>It's not recommended to run n98-magerun as root user</error>";

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
            Events::RUN_BEFORE => 'checkRunningAsRootUser'
        ];
    }

    /**
     * Display a warning if a running n98-magerun as root user
     *
     * @param Event $event
     * @return void
     */
    public function checkRunningAsRootUser(Event $event)
    {
        if ($this->_isSkipRootCheck()) {
            return;
        }

        $config = $event->getApplication()->getConfig();
        if (!$config['application']['check-root-user']) {
            return;
        }

        // display if current user is root
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $output = $event->getOutput();
            $output->writeln('');
            $output->writeln(self::WARNING_ROOT_USER);
            $output->writeln('');
        }
    }

    protected function _isSkipRootCheck()
    {
        return OptionParser::init()->hasLongOption('skip-root-check');
    }
}
