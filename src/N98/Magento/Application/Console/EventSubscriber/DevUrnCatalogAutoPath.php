<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application\Console\EventSubscriber;

use Magento\Developer\Console\Command\XmlCatalogGenerateCommand;
use ReflectionException;
use ReflectionObject;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DevUrnCatalogAutoPath
 *
 * Comfort option to automatically set path to ".idea/misc.xml
 *
 * @package N98\Magento\Application\Console\EventSubscriber
 */
class DevUrnCatalogAutoPath implements EventSubscriberInterface
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
            ConsoleEvents::COMMAND => 'autosetIdeaMiscXmlPath',
        ];
    }

    /**
     * Display a warning if a running n98-magerun as root user
     *
     * @param ConsoleCommandEvent $event
     *
     * @return void
     * @throws \ReflectionException
     */
    public function autosetIdeaMiscXmlPath(ConsoleCommandEvent $event)
    {
        if (!$event->getCommand() instanceof XmlCatalogGenerateCommand) {
            return;
        }

        $input = clone $event->getInput();
        if (!$input instanceof ArgvInput) {
            return;
        }

        $command = clone $event->getCommand();

        $input->bind($command->getDefinition());

        $path = null;
        try {
            $path = $input->getArgument('path');
        } catch (\Exception $e) {
            return;
        }

        if ('dev:urn-catalog:generate' !== $path) {
            return;
        }

        $file = $this->detectFile($event);
        if (null === $file) {
            return;
        }

        $argv = $event->getInput();

        if (!$this->canAddToken($argv)) {
            $event->getOutput()->writeln("<info>Path hint <comment>'$file'</comment></info>");
            return;
        }

        $event->getOutput()->writeln("<info>automatically setting path to <comment>'$file'</comment></info>");
        $this->addToken($argv, $file);
    }

    /**
     * @param ConsoleCommandEvent $event
     * @return string
     */
    private function detectFile(ConsoleCommandEvent $event)
    {
        /** @var \N98\Magento\Application $app */
        $app = $event->getCommand()->getApplication();

        $root = $app->getMagentoRootFolder();
        $down = 2;
        do {
            if (is_dir($root . '/.idea')) {
                return $root . '/.idea/misc.xml';
            }
            $root .= '/..';
        } while (is_dir($root) && $down--);
    }

    /**
     * Check if capable to manipulate tokens as needed
     *
     * @link https://github.com/netz98/n98-magerun2/issues/233
     *
     * @param ArgvInput $arg
     * @return bool
     */
    private function canAddToken(ArgvInput $arg)
    {
        $refl = new ReflectionObject($arg);

        return $refl->hasProperty('tokens');
    }

    /**
     * @param ArgvInput $arg
     * @param string $file
     * @return void
     * @throws ReflectionException
     */
    private function addToken(ArgvInput $arg, $file)
    {
        $refl = new ReflectionObject($arg);
        $prop = $refl->getProperty('tokens');
        $prop->setAccessible(true);
        $tokens = $prop->getValue($arg);
        $tokens[] = $file;
        $prop->setValue($arg, $tokens);
    }
}
