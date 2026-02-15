---
title: config:store:get
---

# config:store:get

Get a store config value from all configuration sources.

```sh
n98-magerun2.phar config:store:get [--scope="..."] [--scope-id="..."] [--decrypt] [--update-script] [--magerun-script] [--format[="..."]] [path]
```

**Arguments:**

| Argument | Description        |
|----------|--------------------|
| `path`   | The config path. Wildcards (`*`) are supported. If not set, all items are listed. |

**Options:**

| Option             | Description                                                                  |
|--------------------|------------------------------------------------------------------------------|
| `--scope=SCOPE`    | The config value's scope (`default`, `websites`, `stores`). Default: `default`. |
| `--scope-id=SCOPE-ID`| The config value's scope ID or scope code.                                   |
| `--decrypt`        | Decrypt the config value using crypt key defined in `env.php`.               |
| `--update-script`  | Output as update script lines.                                               |
| `--magerun-script` | Output for usage with `config:store:set`.                                    |
| `--format[=FORMAT]`| Output Format. One of [csv,json,json_array,yaml,xml].                        |

## Enhanced Configuration Reading

:::info Enhanced Implementation

As of this version, `config:store:get` now reads configuration values from **all sources** that Magento uses, not just the database. This means you get the actual effective configuration values that Magento uses at runtime.

:::

### Configuration Priority Order

The command now correctly follows Magento's configuration priority order:

1. **XML default configuration files** (lowest priority)
2. **`config.php`** (deployment configuration)
3. **`env.php`** (environment configuration)
4. **Database** (`core_config_data` table - highest priority)

### Key Benefits

- **Truthful Values**: Shows the actual configuration values that Magento uses
- **Powerful Search**: Retains unique wildcard search capabilities
- **Perfect Drop-in**: Zero breaking changes, full backward compatibility
- **Consistent Behavior**: Now matches Magento's native behavior but with enhanced search

### Use Cases

This enhancement is particularly useful when:

- Configuration is managed through `env.php` for environment-specific settings
- Deployment configuration is stored in `config.php` 
- You need to verify which values are actually being used by Magento
- Debugging configuration issues where database values might be overridden

**Help:**

If path is not set, all available config items will be listed. path may contain wildcards (`*`)

**Examples:**

```sh
# Get actual session lifetime (reads from env.php if configured there)
n98-magerun2.phar config:store:get admin/security/session_lifetime

# Find all Yotpo configuration with real effective values
n98-magerun2.phar config:store:get '%yotpo%'

# Get all admin configs with accurate values from all sources
n98-magerun2.phar config:store:get 'admin/*'

# Export current effective configuration as magerun script
n98-magerun2.phar config:store:get web/* --magerun-script
```

