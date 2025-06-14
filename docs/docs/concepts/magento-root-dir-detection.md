---
title: Magento Root Directory Detection
---

One of the main advantages of n98-magerun2 is the automatic detection of the Magento Root.

If you run the tool with `-vvv` option, you can see how the tool tries to find the Magento Root.

## --root-dir Option

It is possible to set the Magento Root via the `--root-dir` option. This reduces the I/O operations but is inconvenient.

Example:

`n98-magerun2.phar --root-dir="/path/to/Magento"`

## Stop-File

The automatic detection can be stopped by placing a `.n98-magerun2` file in a Project Root. That is interesting if the Magento Root is not the Project Root.

The "stop file" contains the relative path to the Magento Root from the current position of the "stop file".

Example:

```
.                        -- Project Root
├── .n98-magerun2        -- "Stop file" with content "www".
├── .n98-magerun2.yaml   -- Alternative Project Config
└── www                  -- Magento Root folder
```

If you run the tool with `-vvv` option, you can see if the stop file could be found.
