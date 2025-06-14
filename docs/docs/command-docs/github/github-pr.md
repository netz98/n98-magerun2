---
title: github:pr
---

# github:pr

Gets infos about Github Pull Requests.

:::note
If no Github Repository is defined by `---repository` (-r) option the default Magento 2 Github Repository `magento/magento2` is used. For the [Mage-OS](https://github.com/mage-os/mageos-magento2) repository, use the shortcut option `--mage-os`.
:::

:::warning
The command uses the GitHub API which has rate limits for unauthenticated users.
:::

:::tip
To avoid the rate limit, set a GitHub token in your environment (`GITHUB_TOKEN`) or use the `--github-token` option.
:::

## Usage
```sh
# Magento 2 Open Source
n98-magerun2.phar github:pr <number>
# Mage-OS
n98-magerun2.phar github:pr --mage-os <number>
```

## Arguments
| Argument | Description         |
|----------|---------------------|
| `number` | Pull Request Number |

## Options
| Option                         | Description                                                                 |
|--------------------------------|-----------------------------------------------------------------------------|
| `-r, --repository[=REPOSITORY]`| Repository to fetch from [default: "magento/magento2"]                      |
| `--mage-os`                    | Shortcut option to use the mage-os/mageos-magento2 repository.              |
| `-d, --patch`                  | Download patch and prepare it for applying                                  |
| `-a, --apply`                  | Apply patch to current working directory                                    |
| `--diff`                       | Raw diff download                                                           |
| `--json`                       | Show pull request data as json                                              |
| `--github-token[=GITHUB-TOKEN]`| Github API token to avoid rate limits (can also be set via ENV variable GITHUB_TOKEN) |

## Example: Create a patch file from PR
```sh
n98-magerun2.phar github:pr --patch <number>
```
