---
title: Adobe Commerce Cloud (PaaS)
---

It is recommended to install n98-magerun2 during build phase of a Magento Cloud deployment. That is better for security, because the filesystem is then readonly.

To install the tool, the .magento.app.yaml file has to be modified.

```yaml
hooks:
    # We run build hooks before your application has been packaged.
    build: |
        set -e
        php ./vendor/bin/ece-tools run scenario/build/generate.xml
        php ./vendor/bin/ece-tools run scenario/build/transfer.xml

        # ADD THIS LINES BELOW TO YOUR BUILD STEP DEFINITION

        curl -O https://files.magerun.net/n98-magerun2.phar \
                && mv n98-magerun2.phar bin/n98-magerun2 \
                && chmod +x bin/n98-magerun2 \
                && echo "alias mr2='~/bin/n98-magerun2'" >> ~/.bash_profile
```

After a successful deployment the tool can be used in a SSH session by typing ``mr2``.

The `magento-cloud` tool can also be used.

``magento-cloud ssh -e master bin/n98-magerun2``

Cloud systems are read-only file systems, for creation of stripped database dumps you can ssh to the system
``magento-cloud ssh`` there you can create the dump using ``bin/n98-magerun2 db:dump --strip="@development" -cgz -tsuffix ./var/name_for_dump_file.sql``.

## Option 2 (super proper way)

Extending the `ece-tools` scenario is the preferred way to install n98-magerun2 on Adobe Commerce Cloud. This allows you to keep your customizations in version control and ensures that they are applied consistently across all environments.

This requires an own XML file provided by a local module.
