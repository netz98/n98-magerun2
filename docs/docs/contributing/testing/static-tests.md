---
title: Static Tests
---

## Code Style

:::note
The project uses PHP-CS-Fixer for code style (`.php-cs-fixer.php`) and PHPStan for static analysis (`phpstan.neon.dist`).
:::

To check code style:
```bash
vendor/bin/php-cs-fixer fix --dry-run
```

To fix code style issues:
```bash
vendor/bin/php-cs-fixer fix
```

## Static Analysis

To run static analysis:
```bash
vendor/bin/phpstan analyse
```
