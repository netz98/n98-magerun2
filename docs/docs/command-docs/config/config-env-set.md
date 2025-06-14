---
title: config:env:set
---

# config:env:set

Set a single value in env.php by providing a key and an optional value. The command will save an empty string as default value if no value is set.

Sub-arrays in config.php can be specified by adding a "." character to every array.

```sh
n98-magerun2.phar config:env:set <key> [<value>] [--input-format=INPUT-FORMAT]
```

**Options:**

| Option                        | Description                                         |
|-------------------------------|-----------------------------------------------------|
| `--input-format=INPUT-FORMAT` | Input Format. One of [plain,json] [default: "plain"] |

You can also choose to provide a json text argument as value, by using the optional `--input-format=json` flag.
This will allow you to add values that aren't a string but also other scalar types.

Examples:

```sh
n98-magerun2.phar config:env:set backend.frontName mybackend
n98-magerun2.phar config:env:set crypt.key bb5b0075303a9bb8e3d210a971674367
n98-magerun2.phar config:env:set session.redis.host 192.168.1.1
n98-magerun2.phar config:env:set 'x-frame-options' '*'

n98-magerun2.phar config:env:set --input-format=json queue.consumers_wait_for_messages 0
n98-magerun2.phar config:env:set --input-format=json directories.document_root_is_pub true
n98-magerun2.phar config:env:set --input-format=json cron_consumers_runner.consumers '["some.consumer", "some.other.consumer"]'
```

