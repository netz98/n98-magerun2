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
- Configure Composer for reproducible builds
- Compile the PHAR file
- Set the timestamp to the last commit time for reproducible builds
- Verifies the PHAR signature
- Makes the PHAR executable

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
