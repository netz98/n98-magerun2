---
title: Table Output Formats
sidebar_label: Table Output Formats
---

# Table Output Formats

Commands that display tabular data provide a shared `--format` option. The option is available on commands such as `cache:list`, `customer:list`, `indexer:list`, and `sys:cron:list`.

## Supported Formats

| Format | Description |
| --- | --- |
| `csv` | Comma-separated values with a header row. Suitable for spreadsheets and general data exchange. |
| `tsv` | Tab-separated values with a header row. Useful when values commonly contain commas. |
| `json` | Pretty-printed JSON object output. |
| `json_array` | Pretty-printed JSON array output. Use this when consumers expect a JSON array rather than an object. |
| `jsonl` | One JSON object per line. Also known as JSON Lines. Useful for streaming and shell pipelines. |
| `yaml` | YAML output for human-readable structured data. |
| `markdown` | Pipe-delimited Markdown table output for documentation and issue trackers. |
| `xml` | XML output wrapped in a `<table>` element. |

## Format Aliases

The following aliases produce the same output as their canonical format:

| Alias | Format |
| --- | --- |
| `yml` | `yaml` |
| `md` | `markdown` |
| `ndjson` | `jsonl` |

Aliases are case-insensitive, as are canonical format names.

## Examples

Export an indexer list as CSV:

```sh
n98-magerun2.phar indexer:list --format=csv
```

Generate a Markdown table:

```sh
n98-magerun2.phar customer:list --format=markdown
```

Process one JSON record at a time:

```sh
n98-magerun2.phar sys:cron:list --format=jsonl | while read -r row; do
    printf '%s\n' "$row"
done
```

Use an alias when preferred:

```sh
n98-magerun2.phar config:search "web/*" --format=yml
```

## Invalid Formats

An unsupported value does not fall back to the default terminal table. The command exits with an error that identifies the invalid format and lists the supported canonical formats.

```text
Unknown output format "toml". Supported formats: csv, tsv, json, json_array, jsonl, yaml, markdown, xml.
```

TOML and TOON are intentionally not supported output formats. TOML is primarily intended for configuration, while TOON is a specialized, less widely supported notation for token-efficient data exchange. JSON, CSV, YAML, and Markdown provide better interoperability for the command-line use cases covered by n98-magerun2.
