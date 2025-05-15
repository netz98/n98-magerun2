## DDEV Command Guidelines for AI Assistant

This document outlines the approved `ddev` commands that the AI assistant is permitted to execute within the development environment. The AI assistant should only use commands from this list unless explicitly instructed otherwise by the user for a specific, one-off task.

The primary goal is to enable the AI to assist with common development workflows related to `ddev` and `n98-magerun2` without risking unintended side effects or requiring complex decision-making.

**Allowed DDEV Commands:**

* **`ddev start`**: Starts the DDEV project. Useful for ensuring the environment is running before executing other commands.
* **`ddev stop`**: Stops the DDEV project containers. Can be used to free up resources when the project is not actively being used. *Note: This does not remove project data.*
* **`ddev restart`**: Restarts the DDEV project. Useful for applying configuration changes or resolving minor issues.
* **`ddev list`**: Lists all DDEV projects. Useful for the AI to understand which projects are available or their current status.
* **`ddev describe`**: Provides a detailed description of the current DDEV project. Useful for the AI to gather information about the project's configuration, URLs, and status.
* **`ddev exec <command>`**: Executes a shell command inside the web container. This is a powerful command that allows the AI to run arbitrary commands within the project's environment. The AI should use this primarily for running project-specific scripts, `n98-magerun2` commands, or other necessary tools *after* confirming the project is started.

    * *Constraint:* The AI must be cautious about the commands executed via `ddev exec` and ideally only run commands explicitly requested or known to be safe within the project context.

* **`ddev ssh <service>`**: Starts a shell session in a specified container (defaults to web). While the AI itself won't have an interactive session, this command *could* potentially be used in combination with `ddev exec` if needed to target a specific service container, though `ddev exec` is generally preferred for simple command execution in the web container.
* **`ddev logs <service>`**: Gets the logs from the specified service container (defaults to all). Useful for debugging or monitoring the environment.
* **`ddev composer <command>`**: Executes a Composer command within the web container. Essential for managing project dependencies.
* **`ddev import-db`**: Imports a SQL dump file into the project's database. Useful for setting up or refreshing the database. The AI should confirm the source file before executing.
* **`ddev export-db`**: Dumps the project's database to a file or stdout. Useful for creating backups or sharing the database. The AI should confirm the destination or usage.
* **`ddev mr2-dev-23 <command>`**: Runs an `n98-magerun2` command specifically against a Magento 2.3 environment within the web container.
* **`ddev mr2-dev-24 <command>`**: Runs an `n98-magerun2` command specifically against a Magento 2.4 environment within the web container.
* **`ddev qa <command>`**: Runs `n98-magerun2` code checks within the web container. Useful for code quality tasks.
* **`ddev unit-test-23 <command>`**: Runs `n98-magerun2` PHPUnit tests against Magento 2.3 within the web container.
* **`ddev unit-test-24 <command>`**: Runs `n98-magerun2` PHPUnit tests against Magento 2.4 within the web container.
* **`ddev version`**: Prints the DDEV version and component versions. Useful for the AI to report on the environment setup.
* **`ddev xdebug <on|off>`**: Enables or disables Xdebug within the web container. Useful for debugging tasks.

**Commands NOT Allowed (Unless Explicitly Instructed):**

The AI assistant should **avoid** using the following commands due to their potential impact or need for human oversight:

* `ddev delete`: Removes project data.
* `ddev poweroff`: Stops all projects globally.
* `ddev config`: Modifies project configuration.
* `ddev clean`: Removes DDEV-created items.
* `ddev self-upgrade`: Upgrades DDEV itself.
* `ddev hostname`: Manages hostfile entries.
* `ddev pull`/`ddev push`: Interacts with remote providers (requires specific configuration and caution).
* `ddev snapshot`: Creates database snapshots (can consume space and requires management).
* `ddev add-on`, `ddev auth`, `ddev blackfire`, `ddev cake`, `ddev craft`, `ddev dotenv`, `ddev get-magento-source`, `ddev ide`, `ddev install-*`, `ddev launch`, `ddev mailpit`, `ddev mariadb`, `ddev mysql`, `ddev mutagen`, `ddev npm`, `ddev nvm`, `ddev php`, `ddev phpmyadmin`, `ddev share`, `ddev xhgui`, `ddev xhprof`, `ddev yarn`: These are either too specific, launch UIs, or involve configurations/actions best left to direct user control.

This guideline provides a safe and effective set of commands for the AI assistant to manage and interact with your DDEV environment for `n98-magerun2` development.
