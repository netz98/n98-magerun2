---
title: CMS, Sales, and Composer Commands
---
### Toggle CMS Block status

Toggles the status for a CMS block based on the given Block identifier.

```sh
n98-magerun2.phar cms:block:toggle <blockId>
```
**Arguments:**
| Argument  | Description      |
|-----------|------------------|
| `blockId` | Block identifier |


### Add Sales Sequences for a given store

Create sales sequences in the database if they are missing, this will recreate profiles to.

```sh
n98-magerun2.phar sales:sequence:add [<store>] 
```
**Arguments:**
| Argument | Description        |
|----------|--------------------|
| `store`  | The store code or ID |


If store is omitted, it'll run for all stores.

*Note: It is possible a sequence already exists, in this case nothing will happen, only missing tables are created.*

### Remove Sales Sequences for a given store

Remove sales sequences from the database, warning, you cannot undo this, make sure you have database backups.

```sh
n98-magerun2.phar sales:sequence:remove [<store>] 
```
**Arguments:**
| Argument | Description        |
|----------|--------------------|
| `store`  | The store code or ID |


If store is omitted, it'll run for all stores. When the option `no-interaction` is given, it will run immediately
without any interaction.
Otherwise it will remind you and ask if you know what you're doing and ask for each store you are running it on.

*Note: .*

### Composer Redeploy Base Packages

If files are missing after a Magento updates it could be that new files were added to the files map in the base packages
of Magento. The `composer:redeploy-base-packages` command can fix this issue.

```sh
n98-magerun2.phar composer:redeploy-base-packages
```
