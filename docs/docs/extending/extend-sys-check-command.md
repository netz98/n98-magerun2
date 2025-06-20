---
title: Extend sys:check Command
---

The `sys:check` command in n98-magerun is a powerful tool for verifying the health of a Magento installation. It features a modular architecture that allows developers to add their own custom checks. This guide explains how to create and register your own checks.

# Core Concept: Modular Checks

The sys:check command discovers available checks through configuration. Checks are organized into groups, and these groups are registered in a YAML config file. This allows you to define new checks on a per-project basis or create a distributable n98-magerun module.

## Creating a Custom Check

A check is a PHP class that implements a specific interface. The results of the check are then added to a ResultCollection.

### Check Interfaces

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

- The `check()` method receives a Result\Collection object.
- You create a new Result object using `$results->createResult()`.
    - Set a descriptive message with `setMessage()`.
    - Set the outcome with `setStatus()`. Possible values are:
        - `\N98\Magento\Command\System\Check\Result::STATUS_OK`
        - `\N98\Magento\Command\System\Check\Result::STATUS_WARNING`
        - `\N98\Magento\Command\System\Check\Result::STATUS_ERROR`

## Accessing Command Configuration and Context

Sometimes a check needs more context, like access to the command's configuration or the command object itself.

- `\N98\Magento\Command\CommandConfigAware`: Implement this interface to get the command's configuration injected. Your class will need a `setCommandConfig(array $config)` method.
- `\N98\Magento\Command\CommandAware`: Implement this interface to get the command object itself injected. Your class will need a `setCommand(Command $command)` method.

## Registering Your Check

After creating your check class, you must register it in a YAML config file so that n98-magerun2 can find it.

Create a YAML file (`n98-magerun2.yaml`) in your project (`app/etc`) or module and add the following configuration:

```yaml
commands:
  N98\Magento\Command\System\CheckCommand:
    checks:
      my-check-group:
        - N98\Magento\Command\System\Check\Custom\MemoryLimitCheck
```

- Checks are registered under the sys:check command (`N98\Magento\Command\System\CheckCommand`).
- You can define your own check groups (e.g., `my-check-group`).
- Each check within a group is a fully qualified class name (FQCN).

**Example from the default configuration:**

```yaml
commands:
  N98\Magento\Command\System\CheckCommand:
    checks:
      settings:
        - N98\Magento\Command\System\Check\Settings\SecureBaseUrlCheck
        - N98\Magento\Command\System\Check\Settings\UnsecureBaseUrlCheck
        - N98\Magento\Command\System\Check\Settings\SecureCookieDomainCheck
        - N98\Magento\Command\System\Check\Settings\UnsecureCookieDomainCheck
      filesystem:
        - N98\Magento\Command\System\Check\Filesystem\FoldersCheck
        - N98\Magento\Command\System\Check\Filesystem\FilesCheck
      php:
        - N98\Magento\Command\System\Check\PHP\ExtensionsCheck
        - N98\Magento\Command\System\Check\PHP\BytecodeCacheExtensionsCheck
      mysql:
        - N98\Magento\Command\System\Check\MySQL\VersionCheck
        - N98\Magento\Command\System\Check\MySQL\EnginesCheck
      env:
        - N98\Magento\Command\System\Check\Env\ExistsCheck
        - N98\Magento\Command\System\Check\Env\KeyExistsCheck
        - N98\Magento\Command\System\Check\Env\CacheTypesCheck
      hyva:
        - N98\Magento\Command\System\Check\Hyva\InstallationBasicComposerPackagesCheck
        - N98\Magento\Command\System\Check\Hyva\MissingGraphQLPackagesCheck
        - N98\Magento\Command\System\Check\Hyva\IsCaptchaEnabledCheck
        - N98\Magento\Command\System\Check\Hyva\IncompatibleBundledModulesCheck
```

Now, when you run `n98-magerun2.phar sys:check`, your custom check group and the checks within it will be available. You can run all checks or just your group:

```bash
# Run all checks, including yours
n98-magerun2.phar sys:check

# Run only the checks in your custom group
n98-magerun2.phar sys:check --group=my-check-group
```

By following these steps, you can extend the sys:check command with custom validations tailored to your project's specific needs.
