---
title: Internal Config Loading
---

The internal config loading process in n98-magerun2 is robust and extensible, allowing configuration at multiple levels. The process is orchestrated by the `Application` class and relies on `Config`, `ConfigLocator`, and `ConfigurationLoader` to discover, load, and merge configuration files, ensuring that user and project-specific settings are respected.


## Technical Overview: Internal Config Loading Process

n98-magerun2 uses a layered configuration system, loading and merging config files from multiple sources. The process is managed by several key classes and methods:

### Key Classes and Methods

- **`N98\Magento\Application\Config`**: Central class for managing configuration data. Handles loading, merging, and providing access to config values.
- **`N98\Magento\Application\ConfigLocator`**: Responsible for discovering config files in user, project, and stop-file locations.
- **`N98\Magento\Application\ConfigInfo`**: Represents metadata about each config file (type and path).
- **`N98\Magento\Application\ConfigurationLoader`**: Handles the actual loading and merging of config files in two stages.
- **`N98\Magento\Application`**: The main application class, orchestrates the config loading process in its `init()` method.

### Config Loading Process

1. **Partial Config Load (Stage One)**
   - In `Application::init()`, a `Config` object is created and `Config::loadPartialConfig()` is called.
   - This loads basic config files (e.g., dist, system, user) to determine initial settings and command aliases.

2. **Magento Detection**
   - The application attempts to detect the Magento root directory, which may affect which config files are loaded (e.g., project config in `app/etc/`).

3. **Full Config Load (Stage Two)**
   - After Magento detection, `ConfigurationLoader::loadStageTwo()` is called.
   - This loads additional config files, such as project and stop-file configs, using the discovered Magento root.
   - The `ConfigLocator` is used to find the correct file paths for user, project, and stop-file configs.

4. **Config Merging**
   - All discovered config files are merged in a specific order:
     1. Dist (default)
     2. System
     3. User
     4. Plugin
     5. Project
     6. Stop-file (if present)
   - Later files override earlier ones, allowing for user and project-specific customizations.

5. **Config Access and Usage**
   - The merged config is available via the `Config` object, which provides methods to retrieve config values and register custom commands, helpers, and autoloaders.

### Example: Config Discovery

- **User Config**: Located in the user's home directory (e.g., `~/.n98-magerun2.yaml`). Discovered by `ConfigLocator::getUserConfigFile()`.
- **Project Config**: Located in the Magento root at `app/etc/n98-magerun2.yaml`. Discovered by `ConfigLocator::getProjectConfigFile()`.
- **Stop-File Config**: Optionally, a config file in the directory containing a stop file. Discovered by `ConfigLocator::getStopFileConfigFile()`.
