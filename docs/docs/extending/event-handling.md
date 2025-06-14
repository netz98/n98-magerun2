---
title: Event Handling
sidebar_label: Event Handling
---

Event handling is a programming paradigm that enables different parts of an application to communicate in a loosely coupled way. In this model, components can dispatch (emit) events when something happens, and other components can listen for these events and react accordingly. This approach allows for extensibility and modularity, as new behaviors can be added without modifying the core logic.

## Symfony CLI Command Events

n98-magerun2 is built on top of the Symfony Console component, which provides a robust event system for CLI commands. Key events include:

- **COMMAND**: Dispatched before a command is executed. Listeners can modify input/output or prevent execution.
- **TERMINATE**: Dispatched after a command finishes. Useful for cleanup or logging.
- **ERROR**: Dispatched if an exception occurs during command execution. Listeners can handle or log errors.

By subscribing to these events, developers can hook into the command lifecycle, add custom logic, or alter command behavior without changing the command's code.

## n98-magerun2 Events

n98-magerun2 extends the event-driven approach by providing its own events within the application. These events allow modules, plugins, or custom scripts to react to specific actions or states within the n98-magerun2 runtime. For example, events may be dispatched before or after certain commands, during application bootstrapping, or when interacting with Magento internals.

One example is the `\N98\Magento\Application\Console\Events::RUN_BEFORE` event, which is dispatched before the application runs a command. This allows listeners to perform actions or modify state prior to command execution.

By leveraging both Symfony's and n98-magerun2's event systems, developers can build powerful extensions, automate workflows, and customize the CLI tool to fit their needs—all while keeping code decoupled and maintainable.

## Registering Event Subscribers via YAML Configuration

Event subscribers can be registered in n98-magerun2 using any supported YAML [configuration](./configuration.md). This allows you to add custom or core event subscribers that will be automatically registered with the event dispatcher when the application starts.

For example, to register core event subscribers:

```yaml
event:
  subscriber:
    - N98\Magento\Application\Console\EventSubscriber\CheckCompatibility
    - N98\Magento\Application\Console\EventSubscriber\CheckRootUser
    - N98\Magento\Application\Console\EventSubscriber\VarDirectoryChecker
    - N98\Magento\Application\Console\EventSubscriber\DevUrnCatalogAutoPath
```

Each class listed under `event.subscriber` must implement the `EventSubscriberInterface` and will be automatically registered to listen for the events it subscribes to.

## Examples

### Listening to Symfony CLI Command Events

You can listen to Symfony Console events by registering an event subscriber. For example, to listen for the `COMMAND` event:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\ConsoleEvents;

class MyCommandSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        // Your logic here
    }
}
```

Register your subscriber with the event dispatcher used by the application.

### Listening to n98-magerun2 Events

To listen for the `\N98\Magento\Application\Console\Events::RUN_BEFORE` event, create a subscriber like this:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use N98\Magento\Application\Console\Events;

class MyMagerunSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::RUN_BEFORE => 'onRunBefore',
        ];
    }

    public function onRunBefore($event)
    {
        // Your logic here
    }
}
```

Register your subscriber according to the n98-magerun2 extension/module system.
