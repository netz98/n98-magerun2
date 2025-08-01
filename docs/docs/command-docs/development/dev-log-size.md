---
title: dev:log:size
---

Get size of log files in var/log directory.

## Usage

```bash
n98-magerun2 dev:log:size [options]
```

## Description

This command displays the size of all log files in the `var/log` directory. Magento 2 typically generates multiple log files including `system.log`, `debug.log`, `exception.log`, and others depending on your configuration and installed modules.

**Options:**

| Option               | Shortcut | Description                                                |
|----------------------|----------|------------------------------------------------------------|
| `--human-readable`   | -H       | Show file sizes in a human readable format (e.g., KB, MB). |
| `--sort-by-size`     | -s       | Sort files by size, showing largest files first.           |
| `--filter=PATTERN`   | -f       | Filter log files by name pattern. Only files containing the specified pattern will be displayed. |
| `--format=FORMAT`    |          | Output format for the table. Supported: csv, json, markdown, table, xml, and more. |


## Examples

### Basic usage
```bash
n98-magerun2 dev:log:size
```

Shows all log files with their sizes in bytes.

### Human-readable sizes
```bash
n98-magerun2 dev:log:size --human-readable
```

Shows all log files with sizes formatted as KB, MB, etc.

### Sort by file size
```bash
n98-magerun2 dev:log:size --sort-by-size --human-readable
```

Shows log files sorted by size (largest first) with human-readable formatting.

### Filter specific log files
```bash
n98-magerun2 dev:log:size --filter system
```

Shows only log files containing "system" in their filename.

### Combine options
```bash
n98-magerun2 dev:log:size --filter exception --sort-by-size --human-readable
```

Shows exception-related log files, sorted by size with human-readable formatting.

### Output in different formats
```bash
n98-magerun2 dev:log:size --format=csv
```

Prints the log file table in CSV format. You can also use `json`, `markdown`, `xml`, etc. for integration or reporting purposes.

## Output

The command displays a table with the following columns:

- **Log File**: The name of the log file
- **Size**: File size (in bytes or human-readable format)
- **Last Modified**: When the file was last modified

At the end, it shows a summary with the total number of files and total size.

## Use Cases

- **Log Management**: Identify large log files that may need rotation or cleanup
- **Debugging**: Quickly find which log files are actively being written to
- **Disk Space**: Monitor log file sizes to prevent disk space issues
- **Maintenance**: Prepare for log cleanup by understanding file sizes

## Related Commands

- [dev:report:count](./dev-report-count.md) - Count report files
- [db:dump](../db/db-dump.md) - Database dump (has log table groups)
