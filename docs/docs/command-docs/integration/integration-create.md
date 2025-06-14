---
title: integration:create
sidebar_label: integration:create
---

Create a new integration (WebAPI access token).

:::tip
Use this command to generate access credentials for third-party applications or services that need to interact with your Magento store via the WebAPI.
:::

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

:::warning
If no ACL resource is defined, the new integration token will be created with FULL ACCESS. To restrict access, provide a list of ACL resources using the `--resource` option.
:::
