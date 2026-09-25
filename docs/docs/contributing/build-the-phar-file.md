---
title: Build the phar file
---

The project includes a build script that creates the n98-magerun2.phar file:

```bash
./build.sh
```

:::info
The build script uses the tool [box](https://github.com/box-project/box) to compile the PHAR file. If you don't have `box.phar` installed, the script will download it automatically. The content of the phar is defined in the `box.json` file, which specifies the files to include, the main entry point, and other metadata.
:::

This script does the following:

- Checks for required dependencies
- Download the box.phar tool if needed
- Validates the bundled `db:compatibility` dataset (`./res/db-compatibility.json`) against its JSON Schema, and refreshes it from `MAGERUN_DB_COMPATIBILITY_DATASET_URL` when that environment variable is set (skip with `--skip-dataset-fetch`, see below)
- Configure Composer for reproducible builds
- Compile the PHAR file
- Set the timestamp to the last commit time for reproducible builds
- Verifies the PHAR signature
- Makes the PHAR executable

:::info
`./build.sh --skip-dataset-fetch` skips the dataset refresh step entirely (no network requests, no re-validation) and packages `./res/db-compatibility.json` as-is - useful for offline or repeated local builds. Without the flag, the existing snapshot is always re-validated; it is only replaced if `MAGERUN_DB_COMPATIBILITY_DATASET_URL` is set and the fetched dataset passes schema validation. On any fetch or validation failure the previous, already-validated snapshot is kept and a warning is printed - the build only fails if no valid dataset is available at all.
:::

If the script runs successfully, you will find the `n98-magerun2.phar` file in the root directory of the project.
You can then easily execute it with:

```bash
php n98-magerun2.phar
```

or

```bash
./n98-magerun2.phar
```

In the ddev environment, you can also run it with different PHP versions by using the `ddev exec` command:

```bash
ddev exec php n98-magerun2.phar
ddev exec php8.2 n98-magerun2.phar
ddev exec php8.3 n98-magerun2.phar
ddev exec php8.4 n98-magerun2.phar
```
