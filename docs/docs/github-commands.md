---
sidebar_position: 27
title: GitHub Commands
---
## Github

## Pull Requests

Gets infos about Github Pull Requests.
If no Github Repository is defined by `---repository` (-r) option the default
Magento 2 Github Repository `magento/magento2` is used.
For the [Mage-OS](https://github.com/mage-os/mageos-magento2) repository we provide a shortcut option `--mage-os`.

The command uses the GitHub API which has a rate limits for unauthenticated users.
If you want to avoid the rate limit, you can set a Gitkub token in your environment.
The token can be created in your GitHub account settings.
You can set the token as environment variable `GITHUB_TOKEN` or use the `--github-token` option.

```sh

If the command is executed without any options it will show infos about the PR.

```sh
# Magento 2 Open Source
n98-magerun2.phar github:pr <number>
# Mage-OS
n98-magerun2.phar github:pr --mage-os <number>
```
**Arguments:**
| Argument | Description         |
|----------|---------------------|
| `number` | Pull Request Number |

**Options:**
| Option                         | Description                                                                 |
|--------------------------------|-----------------------------------------------------------------------------|
| `-r, --repository[=REPOSITORY]`| Repository to fetch from [default: "magento/magento2"]                      |
| `--mage-os`                    | Shortcut option to use the mage-os/mageos-magento2 repository.              |
| `-d, --patch`                  | Download patch and prepare it for applying                                  |
| `-a, --apply`                  | Apply patch to current working directory                                    |
| `--diff`                       | Raw diff download                                                           |
| `--json`                       | Show pull request data as json                                              |
| `--github-token[=GITHUB-TOKEN]`| Github API token to avoid rate limits (can also be set via ENV variable GITHUB_TOKEN) |


*Create a patch file from PR:*

```sh
n98-magerun2.phar github:pr --patch <number>
```

*Directly apply the patch:*

```sh
# Magento 2 Open Source
n98-magerun2.phar github:pr --patch --apply <number>

# for Mage-OS
n98-magerun2.phar github:pr --mage-os --patch --apply <number>
```

Files of the magento2-base and magento2-ee-base and b2b base packages are currently not handled by the command.

**List only the raw diff:**

```sh
n98-magerun2.phar github:pr --diff <number>
```
