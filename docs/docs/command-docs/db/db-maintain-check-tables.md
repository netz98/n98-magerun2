---
title: db:maintain:check-tables
---

# db:maintain:check-tables

Check database tables.

```sh
n98-magerun2.phar db:maintain:check-tables [options]
```

**Options:**

| Option             | Description                                                                 |
|--------------------|-----------------------------------------------------------------------------|
| `--type[=TYPE]`    | Check type (one of QUICK, FAST, MEDIUM, EXTENDED, CHANGED) [default: "MEDIUM"] |
| `--repair`         | Repair tables (only MyISAM)                                                 |
| `--table[=TABLE]`  | Process only given table (wildcards are supported)                          |
| `--format[=FORMAT]`| Output Format. One of [csv,json,json_array,yaml,xml]                        |
