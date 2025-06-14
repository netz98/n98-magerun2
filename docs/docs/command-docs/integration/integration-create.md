---
title: integration:create
sidebar_label: integration:create
---

Create a new integration (WebAPI access token).

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

