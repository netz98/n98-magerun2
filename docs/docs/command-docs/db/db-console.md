---
title: db:console
---

# db:console

Open MySQL Console

```sh
n98-magerun2.phar db:console [options]
```

**Options:**

| Option                         | Description                                                                                          |
|--------------------------------|------------------------------------------------------------------------------------------------------|
| `--use-mycli-instead-of-mysql` | Use `mycli` as the MySQL client instead of `mysql`                                                   |
| `--no-auto-rehash`             | Same as `-A` option to MySQL client to turn off auto-complete (avoids long initial connection time). |
| `--connection=CONNECTION`      | Select DB connection type for Magento configurations with several databases (default: `default`)     |
