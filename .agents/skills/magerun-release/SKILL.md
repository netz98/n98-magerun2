---
name: magerun-release
description: Technical release process for n98-magerun2
---

# magerun-release

This skill describes the technical release process of the n98-magerun2 tool.

## When to use

Use this skill when a new version of n98-magerun2 needs to be released.

## Instructions

To release a new version, follow these steps:

1. **Investigate the git log to find all changes.**
   - Tool: `git log $(git describe --tags --abbrev=0)..HEAD --oneline`
2. **Determine the next version number.**
   - Follow semantic versioning (SemVer).
3. **Update the `\N98\Magento\Application::APP_VERSION` with the new version number.**
   - File: `src/N98/Magento/Application.php`
4. **Update the `version.txt` with the new version number.**
   - File: `version.txt`
5. **Update the `CHANGELOG.md` with the new version number.**
   - File: `CHANGELOG.md`
   - Ensure all changes from the git log are documented.
6. **Run `release-it` tool.**
   - Command: `npx release-it` (or `vendor/bin/release-it` if available via composer, but typically it's a JS tool)
