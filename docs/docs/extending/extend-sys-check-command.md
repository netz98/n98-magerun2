---
title: Extend sys:check Command
---

The `sys:check` command in n98-magerun is a powerful tool for verifying the health of a Magento installation. It features a modular architecture that allows developers to add their own custom checks. This guide explains how to create and register your own checks.

#ä Core Concept: Modular Checks

The sys:check command discovers available checks through configuration. Checks are organized into groups, and these groups are registered in a config.yaml file. This allows you to define new checks on a per-project basis or create a distributable n98-magerun module.
Creating a Custom Check

A check is a PHP class that implements a specific interface. The results of the check are then added to a ResultCollection.
Check Interfaces

There are three main interfaces you can implement:

- `\N98\Magento\Command\System\Check\SimpleCheck`: The most basic interface. Use this for checks that don't need a specific store or website context.
- `\N98\Magento\Command\System\Check\StoreCheck`: Use this if your check needs to be executed for each configured store view.
- `\N98\Magento\Command\System\Check\WebsiteCheck`: Use this if your check needs to be executed for each configured website.

## Basic Example: SimpleCheck

Here is an example of a simple check that verifies if the PHP memory_limit is set to at least 2 Gigabytes.

```php
<?php
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\Result\Collection;
use N98\Magento\Command\System\Check\SimpleCheck;

class MemoryLimitCheck implements SimpleCheck
{
    /**
     * @param Collection $results
     */
    public function check(Collection $results)
    {
        $memoryLimit = ini_get('memory_limit');
        $result = $results->createResult();
        $result->setMessage('PHP memory_limit is ' . $memoryLimit);

        $limitInBytes = $this->convertToBytes($memoryLimit);

        if ($limitInBytes < 2 * 1024 * 1024 * 1024) {
            $result->setStatus(Result::STATUS_WARNING);
        } else {
            $result->setStatus(Result::STATUS_OK);
        }
    }

    /**
     * @param string $value
     * @return int
     */
    private function convertToBytes($value)
    {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch ($unit) {
            case 'g':
                $value *= 1024;
            // fall-through
            case 'm':
                $value *= 1024;
            // fall-through
            case 'k':
                $value *= 1024;
        }
        return $value;
    }
}
```

**Key Points**:

- The check() method receives a Result\Collection object.
- You create a new Result object using $results->createResult().
    - Set a descriptive message with setMessage().
    - Set the outcome with setStatus(). Possible values are:
        - `\N98\Magento\Command\System\Check\Result::STATUS_OK`
        - `\N98\Magento\Command\System\Check\Result::STATUS_WARNING`
        - `\N98\Magento\Command\System\Check\Result::STATUS_ERROR`

## Accessing Command Configuration and Context

Sometimes a check needs more context, like access to the command's configuration or the command object itself.

- `\N98\Magento\Command\CommandConfigAware`: Implement this interface to get the command's configuration injected. Your class will need a `setCommandConfig(array $config)` method.
- `\N98\Magento\Command\CommandAware`: Implement this interface to get the command object itself injected. Your class will need a
  `setCommand(Command $command)` method.

## Registering Your Check

After creating your check class, you must register it in a yaml config file so that n98-magerun2 can find it.

Create a YAML file (n98-magerun2.yaml) in your project (app/etc) or module and add the following configuration:

```yaml
# n98-magerun2.yaml
commands:
  N98\Magento\Command\System\CheckCommand:
    checks:
      my-check-group:
        -
          id: my-memory-check
          class: 'MemoryLimitCheck'
          description: 'Checks PHP memory_limit'
```

- Checks are registered under the sys:check command (N98\Magento\Command\System\CheckCommand).
- You can define your own check groups (e.g., my-check-group).
- Each check within a group needs a unique id, the fully qualified class name, and a description.

Now, when you run n98-magerun.phar sys:check, your custom check group and the checks within it will be available. You can run all checks or just your group:

# Run all checks, including yours
`n98-magerun2.phar sys:check`

# Run only the checks in your custom group
`n98-magerun2.phar sys:check --group=my-check-group`

By following these steps, you can extend the sys:check command with custom validations tailored to your project's specific needs.
