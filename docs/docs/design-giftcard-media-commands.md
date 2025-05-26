---
title: Design, Giftcard, and Media Commands
---
### design:demo-notice
Toggles demo store notice for a store view.
```sh
n98-magerun2.phar design:demo-notice [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option   | Description                 |
|----------|-----------------------------|
| `--on`   | Switch on                   |
| `--off`  | Switch off                  |
| `--global`| Set value on default scope  |
---

### Generate Gift Card Pool

Generates a new gift card pool.

```sh
n98-magerun2.phar giftcard:pool:generate
```

### Create a Gift Card

```sh
n98-magerun2.phar giftcard:create [--website[="..."]] [--expires[="..."]] [amount]
```

You may specify a website ID or use the default. You may also optionally
add an expiration date to the gift card using the
`--expires` option. Dates should be in `YYYY-MM-DD` format.

### View Gift Card Information

```sh
n98-magerun2.phar giftcard:info [--format[="..."]] [code]
```

### Remove a Gift Card

```sh
n98-magerun2.phar giftcard:remove [code]
```

---

### Dump Media folder

Creates a ZIP archive with media folder content.

```sh
n98-magerun2.phar media:dump [--strip] [<filename>]
```
**Arguments:**
| Argument   | Description   |
|------------|---------------|
| `filename` | Dump filename |
**Options:**
| Option   | Description          |
|----------|----------------------|
| `--strip`| Excludes image cache |

---
