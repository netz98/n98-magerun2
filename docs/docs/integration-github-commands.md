---
title: Integration and Github Commands
---
### Integrations (Webapi Access Tokens)

There are four commands to create, show, list, delete integrations (access tokens).
This commands are very useful for developers.

#### List all existing integrations

```sh
n98-magerun2.phar integration:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


#### Create a new integration

```sh
n98-magerun2.phar integration:create [options] [--] <name> [<email> [<endpoint>]]
```
**Arguments:**
| Argument   | Description               |
|------------|---------------------------|
| `name`     | Name of the integration   |
| `email`    | Email                     |
| `endpoint` | Endpoint URL              |

**Options:**

| Option                                      | Description                                              |
|---------------------------------------------|----------------------------------------------------------|
| `--consumer-key=CONSUMER-KEY`               | Consumer Key (length 32 chars)                           |
| `--consumer-secret=CONSUMER-SECRET`         | Consumer Secret (length 32 chars)                        |
| `--access-token=ACCESS-TOKEN`               | Access-Token (length 32 chars)                           |
| `--access-token-secret=ACCESS-TOKEN-SECRET` | Access-Token Secret (length 32 chars)                    |
| `--resource=RESOURCE` `-r`                  | Defines a granted ACL resource (multiple values allowed) |
| `--format[=FORMAT]`                         | Output Format. One of [csv,json,json_array,yaml,xml]     |


If no ACL resource is defined the new integration token will be created with FULL ACCESS.

If you do not want that, please provide a list of ACL resources by using the `--resource` option.

Example:

```sh
n98-magerun2.phar integration:create "My new integration 10" foo@example.com https://example.com -r Magento_Catalog::catalog_inventory -r Magento_Backend::system_other_settings
```

To see all available ACL resources, please run the command `config:data:acl`.

#### Show infos about existing integration

```sh
n98-magerun2.phar integration:show [--format[=FORMAT]] <name_or_id> [<key>]
```
**Arguments:**
| Argument     | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `name_or_id` | Name or ID of the integration                                               |
| `key`        | Only output value of named param like "Access Token". Key is case insensitive.|
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


Example (print only Access Key):

```sh
n98-magerun2.phar integration:show 1 "Access Key"
```

#### Delete integration

```sh
n98-magerun2.phar integration:delete <name_or_id>
```
**Arguments:**
| Argument     | Description                   |
|--------------|-------------------------------|
| `name_or_id` | Name or ID of the integration |


---

### Github

### Pull Requests

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

---
