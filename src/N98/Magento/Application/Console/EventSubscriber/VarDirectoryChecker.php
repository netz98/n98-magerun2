<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Application\Console\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VarDirectoryChecker
 * @package N98\Magento\Application\Console\EventSubscriber
 */
class VarDirectoryChecker implements EventSubscriberInterface
{
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
            ConsoleEvents::COMMAND => 'checkForVarDirectoryProblem',
        ];
    }

    /**
     * @param ConsoleCommandEvent $event
     * @return bool
     */
    public function checkForVarDirectoryProblem(ConsoleCommandEvent $event): bool
    {
        $tempVarDir = sys_get_temp_dir() . '/magento/var';

        if ((!OutputInterface::VERBOSITY_NORMAL) <= $event->getOutput()->getVerbosity() && !is_dir($tempVarDir)) {
            return true;
        }

        $event->getOutput()->writeln([
            sprintf('<warning>Cache fallback folder %s was found.</warning>', $tempVarDir),
            '',
            'n98-magerun2 is using the fallback folder. If there is another folder configured for Magento, this ' .
            'can cause serious problems.',
            'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions ' .
            'for more information.',
            '',
        ]);

        return false;
    }
}
