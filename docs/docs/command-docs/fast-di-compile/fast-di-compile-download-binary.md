---
title: fast-di-compile:download-binary
---

# fast-di-compile:download-binary

Downloads a verified platform binary for [fast-di-compile](https://github.com/speedupmate/di-compiler), the Rust DI compiler by Anton Siniorg (speedupmate).

```bash
n98-magerun2 fast-di-compile:download-binary
```

The command detects Linux or macOS and x64 or arm64 automatically. It downloads the matching release archive from `speedupmate/di-compiler` and installs the executable at:

```text
bin/fast-di-compile
```

Use `--release` for a specific release, `--platform` to override platform detection, and `--force` to replace a binary that does not match the selected release. `--version` is reserved by Symfony Console for n98-magerun2 itself.

```bash
n98-magerun2 fast-di-compile:download-binary --release=v1.0.3 --platform=linux-arm64
```

## Verification

The command obtains SHA-256 asset digests from the GitHub release API. It verifies the downloaded checksum file and archive before extracting the executable. A binary already present at the target location is preserved unless it matches the selected release or `--force` is passed.

## How Rust Is Used

The downloaded program is a Rust implementation of Magento's DI compilation. The companion `spmt/magento2-spmt-fast-di-compile` module replaces Magento's `setup:di:compile` command with a wrapper when this executable is available. The wrapper passes the Magento root to the Rust binary, which generates DI code and metadata; Magento falls back to its standard PHP compiler if the binary is unavailable or `--standard` is selected.

This command only downloads the compiler binary. A separate `fast-di-compile:install` command will install and enable the companion Magento module in a later release.
