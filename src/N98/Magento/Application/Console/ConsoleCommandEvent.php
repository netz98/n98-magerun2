<?php

namespace N98\Magento\Application\Console;

class ConsoleCommandEvent extends \Symfony\Component\Console\Event\ConsoleCommandEvent
{
    /**
     * Gets the event's name.
     *
     * @return string
     *
     * @deprecated since version 2.4, to be removed in 3.0. The event name is passed to the listener call.
     */
    public function getName()
    {
    }

    /**
     * Sets the event's name property.
     *
     * @param string $name The event name
     *
     * @deprecated since version 2.4, to be removed in 3.0. The event name is passed to the listener call.
     */
    public function setName($name)
    {
    }

    /**
     * Stores the EventDispatcher that dispatches this Event.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     *
     * @deprecated since version 2.4, to be removed in 3.0. The event dispatcher is passed to the listener call.
     */
    public function setDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Returns the EventDispatcher that dispatches this Event.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     *
     * @deprecated since version 2.4, to be removed in 3.0. The event dispatcher is passed to the listener call.
     */
    public function getDispatcher()
    {
    }
}
