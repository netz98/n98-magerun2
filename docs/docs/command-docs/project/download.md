---
title: download
sidebar_label: download
---

Downloads Magento source code into a target directory. This is a **download-only** command - it does not
check platform requirements, generate configuration files, or set up a database. It replaces the download
step of the deprecated `install` command.

:::info
This command supports both an interactive wizard mode and a strict, unattended mode via `--no-interaction`.
:::

Two download strategies are available:

- **`composer`** (default) - runs `composer create-project` for one of the known Magento editions.
- **`git`** - runs `git clone`, intended for Magento core contributors who need a working copy of
  `magento/magento2` or a fork.

The interactive wizard guides you through the choices with short descriptions for each option, offers a
preset picker for the git strategy (official `magento/magento2` core repo, `mage-os/mage-os`, or a custom
entry), and finishes with a summary and a confirmation prompt before anything is downloaded.

:::tip
In a real terminal, the strategy/edition/repository prompts and the final confirmation support arrow-key
navigation. On Windows, or when the command isn't attached to an interactive terminal (CI, piped input),
it automatically falls back to typing the number or key of your choice - the available options are
identical either way.
:::

Interactive wizard:

```sh
n98-magerun2.phar download
```

Non-interactive composer-based download:

```sh
n98-magerun2.phar download --edition=open-source --constraint=2.4.7 --dir=./my-magento-shop --no-interaction
```

Non-interactive git-based download (for Magento core contributors):

```sh
n98-magerun2.phar download --strategy=git --repo=git@github.com:my-fork/magento2.git --dir=./core-contribution --no-interaction
```

The `--repo` option also accepts a GitHub `owner/repo` shorthand, which is expanded to a full clone URL:

```sh
n98-magerun2.phar download --strategy=git --repo=magento/magento2 --dir=./core --no-interaction
```

**Options:**
| Option                              | Description                                                                                          |
|--------------------------------------|-------------------------------------------------------------------------------------------------------|
| `--strategy[=STRATEGY]`              | Download strategy: `composer` or `git` [default: `"composer"`]                                        |
| `--edition[=EDITION]`                | Edition to download (composer strategy): `open-source`, `adobe-commerce`, `mage-os`                    |
| `--constraint[=CONSTRAINT]`          | Version constraint to install, e.g. `2.4.7` (composer strategy). Defaults to the latest stable version |
| `--repository-url[=REPOSITORY-URL]`  | Override the composer repository URL used for the selected edition (composer strategy)                |
| `--repo[=REPO]`                      | Git repository to clone (git strategy). Accepts a full URL or a GitHub `owner/repo` shorthand          |
| `--branch[=BRANCH]`                  | Branch, tag or ref to check out (git strategy). Defaults to the repository default branch              |
| `--dir[=DIR]`                        | Target directory to download into                                                                     |

:::note
The option is named `--constraint` rather than `--version` because `--version`/`-V` is reserved by
Symfony Console to display the application version.
:::

**Editions:**
| Edition          | Composer package                          | Repository                  | Requires auth | Description                                                            |
|------------------|--------------------------------------------|------------------------------|---------------|--------------------------------------------------------------------------|
| `open-source`    | `magento/project-community-edition`        | `https://repo.magento.com`   | Yes           | Community Edition — free, requires a Marketplace account                |
| `adobe-commerce` | `magento/project-enterprise-edition`        | `https://repo.magento.com`   | Yes           | Commerce Edition — commercial license, requires Marketplace credentials |
| `mage-os`        | `mage-os/project-community-edition`         | `https://repo.mage-os.org`   | No            | Mage-OS                                                                  |

:::tip
The list of editions is configurable. It is defined under the `editions` command config key for
`N98\Magento\Command\Project\DownloadCommand` in `config.yaml`, and can be overridden or extended on
project or user level (see the [configuration documentation](/extending/configuration) for details):

```yaml
commands:
  N98\Magento\Command\Project\DownloadCommand:
    editions:
      - name: open-source
        package: magento/project-community-edition
        repository-url: https://repo.magento.com
        requires-auth: true
        default-dir: ./magento-open-source
        description: "Community Edition — free, requires a Marketplace account"
```
:::

**Git repositories:**
| Preset     | Label                          | URL                                          |
|------------|---------------------------------|-----------------------------------------------|
| `magento2` | Magento 2 (official core)       | `https://github.com/magento/magento2.git`    |
| `mage-os`  | Mage-OS (community fork)         | `https://github.com/mage-os/mage-os.git`     |
| `custom`   | Custom                          | enter a full git URL or GitHub `owner/repo` shorthand |

:::tip
The list of preset git repositories is configurable via the `git-repositories` command config key,
following the same pattern as `editions`:

```yaml
commands:
  N98\Magento\Command\Project\DownloadCommand:
    git-repositories:
      - name: magento2
        label: "Magento 2 (official core)"
        url: https://github.com/magento/magento2.git
```
:::

:::info
In interactive mode, before the actual download starts, the wizard shows a summary of the selected
options and asks for confirmation. Running with `--no-interaction` always skips this confirmation
(and every other prompt), as long as all required options are supplied.
:::

:::info
`open-source` and `adobe-commerce` require a Marketplace security key (public/private key pair) from
[marketplace.magento.com](https://marketplace.magento.com/customer/accessKeys/). If run interactively and
no credentials are found, you will be prompted for them and they will be stored via Composer's global
`auth.json`. In non-interactive mode, missing credentials cause the command to fail immediately.
:::

:::info
Before downloading, the command queries the target repository for the real list of versions available
for the selected package. This serves two purposes:

- **Credential/entitlement check**: if the repository rejects the request (invalid or expired
  Marketplace keys, or an account that isn't entitled to the selected edition), the command fails
  immediately with a clear, actionable error instead of the generic error `composer create-project`
  would otherwise produce deep into the install.
- **Version validation**: an exact version passed via `--constraint` (e.g. `2.4.7`) is checked against
  the fetched list, and an unknown version is rejected up front with a few available versions shown as
  suggestions. Range/expression constraints (e.g. `^2.4`, `~2.4.7`) are passed through to Composer
  unvalidated, since Composer itself is best placed to resolve those. In interactive mode, the version
  prompt also offers the real version list as autocomplete suggestions.
:::

:::note
The composer strategy runs a full `composer create-project` - dependencies are resolved and installed
into `vendor/`, not just `composer.json`.
:::

:::tip
After a successful composer-based download, the command suggests the right next step to set up the
application: `bin/magento setup:install`, except for **Mage-OS 3.0 and newer**, which ships a new
interactive installer and gets pointed at `bin/magento install` instead. The version is detected
automatically from the downloaded project - no extra configuration needed.
:::

The target directory must not exist, or must be empty - the command never overwrites or merges into an
existing non-empty directory.
