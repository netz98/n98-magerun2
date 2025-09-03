RECENT CHANGES
==============

9.1.0-beta1 (2025-09-02)
------------------------

- Add: --check option in dev:module:detect-composer-dependencies command (issue #1727)
- Add: admin:user:activate and admin:user:deactivate commands (PR #1761, issue #1760)
- Add: new dev:log:size command to show log file sizes (PR #1749, issue #76)
- Add: --format option to dev:log:size command (e.g. human-readable output) (issue #76)
- Add: Mage-OS 1.3 support in configuration and tests (PR #1776, issue #1775)
- Add: dev console single-process option for improved DX (issue #1110)
- Imp: sys:url:regenerate command refactoring and new options (issue #549)
- Imp: dev:keep-calm now clears static assets
- Fix: cron initialization respects configured pub root (issue #545)
- Fix: bash autocompletion updates (PR #1774, #1765; issues #1773, #1764)
- Fix: use "magento" vendor for generated framework package in module generator (Mage-OS compatibility) (#1239)
- Fix: excluded tables were dumped as structure if --strip option was used (issue #1731)
- Build: update dev services (MariaDB 10.6, OpenSearch 2.19.3)
- Build: bump CI actions and dev dependencies (phpstan, php-cs-fixer, captainhook, psysh, etc.)
- Docs: add docs for admin:user:{activate,deactivate,change-status} and update sys:url:regenerate (PRs #1763, #1770)

9.0.2 (2025-07-21)
------------------

- Fix: db dump command hotfix release

9.0.1 (2025-06-24)
------------------

- fix: phar file had to re-create, due to a release issue

9.0.0 (2025-06-24)
------------------

- Add: keep calm command (PR #1692, issue #499, by Christian Münch)
- Add: extend admin:user:list with --sort option and logdate column (PR #1647, issue #1646)
- Add: support view handling in db:dump and db:drop commands (PR #1672, issue #602)
- Add: enable github tokens in github:pr command (issue #1633, by Christian Münch)
- Add: --add-module-dir option to load external modules (PR #1687, issue #1677, by Christian Münch)
- Add: CSV export option in db:query command (PR #1653, issue #1651)
- Add: Docusaurus documentation and deployment workflow (PR #1643, issue #1643)
- Imp: update to Symfony 6.4 (PR #1608, issue #1608, by Christian Münch)
- Imp: optimize development setup and Magento version detection (by Christian Münch)
- Imp: feat(dev:theme:build-hyva): add --force-npm-install option to always run npm install
- Imp: Update 3rd party dependencies (sigstore/cosign-installer, brace-expansion, captainhook)
- Imp: feat(db): prefer mariadb client tools when available (PR #1695, by Christian Münch)
- Imp: feat(dev:hyva:build): Add --all and --supress-no-theme-found-error in dev:hyva:build command (by Christian Münch)
- Imp: feat(dev:hyva:build): Add check for Hyvä theme in dev:hyva:build command (by Christian Münch)
- Fix: better TTY handling in proxy command (PR #1667, issue #1422, by Christian Münch)
- Fix: PHP 8.4 compatibility updates (PR #1655, issue #1654, by Christian Münch)
- Fix: Port handling in database helper (by Christian Münch)
- Build: CI release workflow enhancements (PR #1657, issue #1657, by Christian Münch)

8.1.1 (2025-04-28)
------------------

- Fix: phar of v8.1.0 was corrupt (by Christian Münch)

8.1.0 (2025-04-28)
------------------

- Add: #1590: Apply GitHub patch directly (by Christian Münch)
- Add: #1591: Admin URI added to table (by Christian Münch)
- Add: #1592: New options to display admin store URLs (by Christian Münch)
- Add: #1593: Describe sys:store:config:base-url:list command (by Christian Münch)
- Add: #1605: magerun config commands (by Christian Münch)
- Imp: #1594: Replace Elasticsearch with OpenSearch in dev setup (by Christian Münch)
- Fix: #1599: Theme choice index (by Christian Münch)
- Fix: #1600: Run create address in an emulated frontend area (by Christian Münch)
- Build: #1602: Use PHP 8.3 for PR builds (by Christian Münch)
- Build: #1603: Use Ubuntu 22.04 in PR-build workflow (by Christian Münch)
- Build: #1604: Update dependencies to the latest Mage-OS requirements (by Christian Münch)
- Build: #1605: Fix Mariadb and OpenSearch services (by Christian Münch)
- Build: #1606: Use correct OPENSEARCH_JAVA_OPTS option for OpenSearch container (by Christian Münch)
- Build: #1607: Set up MariaDB as part of the steps (by Christian Münch)
- Build: #1608: Update and bump dependencies (phpstan, php-cs-fixer, captainhook, webfactory/ssh-agent, psy/psysh) (by dependabot)

8.0.0 (2025-02-24)
------------------

- Major: Drop PHP 7 support
- Add: #1553: New dev:di:preferences:list command (by Christian Münch)
- Add: #1574: New Add mydumper feature to DumpCommand (by Peter Jaap Blaakmeer) 
- Add: #1584: New dev:theme:build-hyva command (by Torben Höhn)
- Imp: #1577: Support Mage-OS 1.0.6 (by Christian Münch) (reported by Fabrizio Balliano)
- Imp: Updated ddev setup to PHP 8.3 and used newer box.phar for phar build (by Christian Münch)
- Imp: Add rollback logic to self-update command (by Christian Münch)
- Imp: Update 3rd party dependencies (phpstan, php-cs-fixer, rmccue/requests, Appleboy SSH Action, Sigstore Cosign Installer, CaptainHook, psr/logger, psysh, fakerphp) to their latest versions
- Fix: #1560: deprecation warning (by Christian Münch)
- Fix: #1563: description of command detect-composer-dependencies (by sir1ke)
- Fix: #1569: Fix typo in README (by Nolwennig-Jeulin)
- Fix: #1572: Import without compression no longer compatible with STDIN (reported by Indy Koning)

7.5.0 (2024-11-25)
------------------

- Add: #1538: zstd and lz4 compression support and auto compression detection on DB import (by ResuBaka).
- Imp: Update dev system to Magento 2.4.7-p2 (by Christian Münch).
- Imp: Update 3rd party dependencies (phpstan, fakerphp, symfony/process, symfony/validator, twig) to their latest versions (by Christian Münch)
- Imp: Streamline Elasticsearch configuration (by Christian Münch)
- Imp: Enhance CI pipelines for dependency updates (by Christian Münch)
- Fix: Minor typo corrections (by Christian Münch)
- Fix: #1519: Fixed casing of DecryptCommand class (by Pieter Hoste)
- Fix: Address issues with MariaDB client tools not supporting `--ssl-mode` (by Christian Münch, reported by Max Fickers)
- Fix: Use the correct function to support PHP 7.4 for file ending checks (by ResuBaka)

7.4.0 (2024-04-26)
------------------

- Add: new Magento Versions 2.4.7, 2.4.6-p5, 2.4.5-p7, 2.4.4-p8 (by Christian Münch)
- Add: New encrypt and decrypt commands (by Indy Koning)
- Add: customer:add-address command (by Christian Walter)
- Imp: Update 3rd party dependencies (phpunit, symfony, php-cs-fixer, phpstan, psysh, twig)
- Fix: #1406: Quotes all arguments used by MagentoCoreProxyCommand (by Christian Münch)
- Imp: #1441: Make integration email and endpoint url optional (reported by Sergii Repin)
- Imp: README.md - converted spaces (by Matheus Gontijo)

7.3.1 (2024-01-31)
------------------

- Fix: dev:console broken after psysh update (by Christian Münch)

7.3.0 (2024-01-31)
------------------

- Add: #1301: Config Search Command (by Christian Münch)
- Imp: #1367: Filter non action classes in route:list output (by Christian Münch)
- Imp: #1308: Use phar file path as fallback (by Christian Münch, Reported by Kostadin A.)
- Imp: Allow to place and n98-magerun2.phar directly in a Magento installation without --root-dir option (Reported by Kostadin A.)
- Imp: Update 3rd party dependencies (php-cs-fixer, captainhook, psysh, phpfaker, phpstan, phpunit, symfony, twig)
- Fix: #1304: Remove non-routes from route:list (Fix by Bohdan Bakalov)
- Fix: #1389: TypeError (Reported by thecodecook14)
- Fix: #1396: Escape shell command before processing by Symfony StringInput (Reported by Denis Mir)

7.2.0 (2023-10-10)
------------------

- Add: #1320: Mage-OS ddev setup (by Christian Münch)
- Add: #1318: Mage-OS 1.0.0 for installer (by Christian Münch)
- Add: #1282: Adding Magento 2.4.6-p2 (by Guillaume Arino)
- Add: #1275: New command cache:remove:id (by Christian Münch)
- Add: #1274: Clear media cache command (by Christian Münch)
- Add: #1255: Add decrypt option to cache:view command (by Christian Münch)
- Imp: #1292: Ask only for credentials if repo.magento.com is used (by Christian Münch)
- Imp: #1272: Convert functional tests to bats (by Christian Münch)
- Imp: #1262: First refactoring of route:list command (by Christian Münch)
- Imp: #1240: Filter XDEBUG_CONFIG env param for core commands (by Christian Münch)
- Imp: Update 3rd party dependencies (php-cs-fixer, phpstan, phpunit, requests, symfony)
- Fix: #1296: Fix short options registration of core commands (by Christian Münch)
- Fix: #1259: Fix not correct routes for route:list command (by Bohdan Bakalov)
- Fix: #1287: integration:delete command doesn't delete associated oauth consumer and oauth token (by Christian Münch)
- Fix: #1254: Add missing docs for cache:view, cache:report

7.1.0 (2023-07-31)
------------------

- Add: #1177: New commands to manage sales sequences (by Jeroen Boersma)
- Add: #1176: New command to redeploy base packages (by Christian Münch)
- Add: #1226: New sys:cron:kill command (by Christian Münch)
- Imp: #1179: New github:pr command (by Christian Münch)
- Imp: #1182: Add debug output if Magento Core Commands cannot be used (by Christian Münch)
- Imp: #1185: Do less compatibility checks (by Christian Münch)
- Imp: Print an error if generation:flush command cannot delete a directory (by Christian Münch)
- Imp: Update 3rd party dependencies (php-cs-fixer, psysh, phpstan, phpunit, requests, symfony)

7.0.3 (2023-04-11)
------------------

- Fix: #1164: Magento Core Commands cannot be loaded. Please verify if "bin/magento" is running (by Christian Münch)

7.0.2 (2023-03-31)
------------------
 
- Fix: #1164: Magento Core Commands cannot be loaded. Please verify if "bin/magento" is running
- Imp: Update 3rd party dependencies (captainhook, phpstan, phpunit, psysh)

7.0.1 (2023-03-22)
------------------

- Fix: #447: Memory usage within Magerun script (by Christian Münch)
- Fix: #1144: Allow to add instead of replace a PSR-4 namespace (by Christian Münch)
- Fix: #1147: Fix command return value and add phar test (by Christian Münch)
- Fix: #1149: Use defined PHP binary for calls to bin/magento (by Christian Münch)
- Imp: Update 3rd party dependencies (captainhook, psysh, php-cs-fixer, phpstan, phpunit)

7.0.0 (2023-03-13)
------------------

- Add: Magento 2.4.6
- Add: Magento 2.4.5-p2
- Add: Magento 2.4.4-p2
- Add: #1041: New command config:data:indexer (by Christian Münch) 
- Add: #1042: New command config:data:mview (by Christian Münch)
- Add: #1126: Add CONTRIBUTING.md and CODE_OF_CONDUCT.md (by Christian Münch)
- Imp: #1123: Make detection debug output more helpful (by Alexander Menk)
- Imp: New internal proxy command to call Magento Core Commands (by Christian Münch)
- Imp: Disabled Magento and config initialization if self-update command runs (by Christian Münch)
- Imp: Update 3rd party dependencies (Symfony, psysh, captainhook, php-cs-fixer, phpunit, twig)
- Del: Support for PHP 7.3
- Del: Remove internal test setup for Composer 1 based Magento installations
- Fix: Initialize Magento only once which should prevent several issues with DB and config. (by Christian Münch)
- Fix: Remove "please select" entry in search:engine:list command (by Christian Münch)

6.1.1 (2023-01-11)
------------------

- Fix: #1066: broken index:list command (by Christian Münch)
- Imp: Update 3rd party dependencies (Symfony, psysh, captainhook, dydev-dot-access-data, phpunit, twig)

6.1.0 (2022-10-19)
------------------

- Add: Magento 2.4.5-p1 / 2.4.4-p2 (by Simon Sprankel)
- Imp: Update 3rd party dependencies (phpstan, requests library)
- Fix: #969: Mark cron as executed (by Pieter Hoste)
- Del: Magento 2.3.7 in ddev test setup (by Christian Münch)

6.0.1 (2022-09-28)
------------------

- Fix: Update twig (security fix)

6.0.0 (2022-09-28)
------------------

- Add: route:list command (by Gowri)
- Add: installer command - Update installable versions (incl. mage-os) (by Christian Münch)
- Add: ddev get-magento-source command for code completion in PhpStorm (by Christian Münch)
- Add: Integrated phpstan in developer setup (by Christian Münch)
- Add: New option to define the modules base dir for module creation in dev:console make:module command (by Christian Münch)
- Add: Auto exit option for dev:console (by Christian Münch)
- Add: New option to run dev:console in PHP script mode (by Christian Münch)
- Imp: Update 3rd party dependencies (twig, phar-utils, phpunit, symfony)
- Imp: Check phpstan in Github Actions (by Christian Münch)
- Imp: Fix all phpstan warnings/errors of level 0 and level 1 (by Christian Münch)
- Imp: ddev setup (installed Magento versions) (by Christian Münch)
- Imp: Add some checks to secure customer:delete command (by Christian Münch)
- Fix: #1028: Check if job config is set (by Christian Münch)
- Fix: #1037: dev:console Code Generator (by Christian Münch)
- Del: Symfony Shell Command - Command was already broken (by Christian Münch)
- Del: Drop active testing support for Magento 2.3.x (some commands could be incompatible due to platform changes)

5.2.0 (2022-08-07)
------------------

- Add: #987: more dev:console debug helper functions (by Christian Münch)
- Add: #1000: additional phar tests (by Christian Münch)
- Imp: #957: Exclude system_config_snapshot from stripped dumps (by Alexander Menk)
- Imp: #1007: Upgrade compatibility list for PHP versions (by Lukasz Bajsarowicz) 
- Imp: #1008: Allow use of pipefail where supported (by Dan Wallis)
- Imp: Change mage-os repo url and disable unstable dev build job (by Christian Münch)
- Imp: Changed installation workflow starting with composer 2.3.7 (by Christian Münch)
- Imp: Update 3rd party dependencies (Symfony, psysh, vfsstream, captainhook, requests lib, faker lib, phpunit, twig)
- Fix: #993: Try to drop only existing databases (by Christian Münch)
- Fix: #996: Customer Debug Functions in Dev Console Dev Helper (by Alexander Menk)
- Fix: #998: Pass empty string as default value to getFileName (by Peter Jaap Blaakmeer)
- Fix: #1015: deprecated: passing null to dirname (by Alexander Menk)
- Fix: #1019: type error in cache-list command on php 8.1 (by Tom Klingenberg)
- Fix: #1024: config:store:get wrong filters applied (by Alexander Dite)
- Fix: typos and wrong infos in help text (by Christian Münch)

5.1.0 (2022-05-06)
------------------

- Add: New table renderer yaml/json_array
- Add: Print integration:show command data as table or only a single value (by Christian Münch)
- Imp: #976: Exclude sequence table data when associated entities are excluded (by Dan Wallis)
- Imp: #979: Dynamically change application name in phar (by Christian Münch)
- Imp: Update 3rd party dependencies (symfony/finder, psysh)
- Imp: Github Actions (Artifact for PRs and dependabot for actions)
- Fix: #984: integration show command (by Christian Münch)
- Fix: #979 Replace strftime function calls (by Artur Jewuła)
- Fix: #977: Prevent Deprecated Functionality under PHP 8.1 (by Jisse Reitsma)
- Fix: #980: Fix scope-id condition (by Christian Münch)
- Fix: #981: Add error handler for possible warnings of autoloader (by Christian Münch)

5.0.2 (2022-04-20)
------------------

- Fix: #966: InstalledVersions.php could not be opened

5.0.1 (2022-04-15)
------------------

- Fix: #964: Broken with guzzle dependency chain

5.0.0 (2022-04-15)
------------------

- Add: #56: Delete customer command (by Andreas Mautz)
- Add: #906: guzzle dependency (by Christian Münch)
- Add: #917: ddev developer setup (by Christian Münch)
- Add: #922: phar functional tests (by Christian Münch)
- Add: #924: dev:translate commands (by Christian Münch)
- Add: #927: PHP 8.1 compatibility (by Christian Münch)
- Add: Commit hash to version command (by Christian Münch)
- Add: #954: dev:module:detect-composer-dependencies command (by Alexander Dite, Jens Richter)
- Add: Test MageOS in Github Actions
- Add: Update version list of install command (MageOS and Adobe)
- Imp: #918: Replace phar build process (by Christian Münch)
- Imp: Github actions to test Magento development versions (by Christian Münch)
- Imp: Replace dependency "adbario/php-dot-notation" with "dflydev/dot-access-data"
- Imp: Update 3rd party dependencies 
       (symfony/yaml, symfony/event-dispatcher, fakerphp, symfony/finder, 
       phpunit, captainhook, psysh, twig, phar-utils, symfony-console-autocomplete)
- Del: Composer download methods in abstract command
- Del: phing developer dependency
- Del: Bundled Composer library
- Fix: #934: install command. Allow for xdebug ^3.0 (by Martin Århof)
- Fix: Error in sys:cron:history command (by Christian Münch)
- Fix: PHP warnings in cache:report command (by Christian Münch)

4.9.1 (2021-12-21)
------------------

- Fix: #901: dev:console command outputs "must be compatible with" error (by Mark Shust)

4.9.0 (2021-12-21)
------------------

- Add: #899: config:env:delete command (by Pieter Hoste) 
- Add: Dependency information in README (by Christian Münch)
- Imp: Updated dependencies (faker, psysh, symfony-console-autocomplete, php-cs-fixer, captainhook)
- Fix: #898: Disable Composer gc during Magerun process (by Christian Münch)

4.8.0 (2021-12-04)
------------------

- Add: #884: Hyvä Support for sys:info and sys:check command (by Christian Münch)
- Add: #875: Environment variable support in Magerun scripts (by Christian Münch)
- Add: #874: Experimental MySQL SSL support (by Karsten Deubert)
- Add: #867: PHPUnit 9 compatibility (by Karsten Deubert)
- Add: New test suite to run core command tests (by Christian Münch)
- Add: Magento 2.4.3 to installer and test pipeline (by Christian Münch)
- Imp: #835: Replace the definer instead of trying to remove it (by Alin Alexandru)
- Imp: #820: Optimize script repo performance by exclude lists (by Christian Münch)
- Imp: #806: Implement input format json for config env set command (by Pieter Hoste)
- Imp: #805: Skip authorization creation during db import if option is specified (by Luca Gallinari)
- Imp: #620: dev:console: Provide convenience functions to dump entities (by Alexander Menk)
- Imp: Error handling in PDO (by Alexander Menk)
- Imp: Updated dependencies
    - dev: captainhook, phpunit, vfsstream, php-cs-fixer
    - application: composer, symfony, phing, psysh, symfony-console-autocomplete, twig
- Imp: Refactoring of Github Actions (Composer 2, Test Pull Requests)
- Fix: #888: Check if file name was provided for db import command (by Torben Höhn)
- Fix: #828: Error on db:import --drop (by Alexander Menk)
- Fix: #824: Restore terminal mode after import (by Jeroen Vermeulen)

4.7.0 (2021-10-27)
------------------

- New: Command db:add-default-authorization-entries (by Christian Münch)
- Imp: Add handling for missing authorization rule/role in db:import (by Christian Münch, Alexander Menk)
- Add: Add table authorization_role to table group @admin (by hannes011)
- Fix: #781 - empty cron expression (by Christian Münch)
- Fix: #789 - sampledata:deploy returns composer error, bin/magento works (by Christian Münch)

4.6.1 (2021-07-14)
------------------

- Fix: broken self-update command (by Christian Münch)

4.6.0 (2021-06-13)
------------------

- Add: ui_bookmark to admin group (by Leland Clemmons)
- Add: inventory_reservation to @sales group (by Dan Wallis)
- Add: mailchimp table group (by Timon de Groot)
- Add: Magento 2.4.2-p1 (by Christian Münch)
- Fix: Changed filename in README where database config comes from. (by Martien Mortiaux)
- Fix: Remove whitespace to fix code violations (by Christian Münch)
- Fix: Remove push trigger for tests / github actions (by Christian Münch)
- Imp: Updated symfony dependencies (console, event-dispatcher, finder, process, validator, yaml) (Dependbot)
- Imp: Updated composer dependencies  (Dependbot)
- Imp: Updated captainhook dependency  (Dependbot)
- Imp: Updated php-cs-fixer dependency  (Dependbot)
- Imp: Updated phpunit dependency  (Dependbot)
- Imp: Updated psy shell dependency  (Dependbot)

4.5.0 (2021-02-14)
------------------

- Add: Magento 2.4.2 (by Christian Münch)
- Imp: Change autoloading from PSR-0 to PSR-4 (by Tom Klingenberg)
- Imp: Updated n98/junit-xml dependency (by Tom Klingenberg)
- Imp: Updated Symfony dependencies (Dependabot)
- Imp: Updated faker dependency (Dependbot)
- Imp: Updated captainhook dependency (Dependbot)
- Imp: Updated php-cs-fixer dependency (Dependbot)
- Imp: Test with PHP 7.4 in github actions (by Christian Münch)
- Fix: When the mysql import fails, make db:import fail as well (by Pieter Hoste)
- Fix: Set php version to 7.4 in Github actions (by Christian Münch)
- Fix: db:dump set correct default value (by Torben Höhn)

4.4.0 (2020-12-24)
------------------

- New: #482: Add a command to change the admin user status (by Melvin Versluijs)
- New: #595: Command to toggle the CMS block status (by Melvin Versluijs)
- New: #662: Github Actions QA Workflow (by Christian Münch)
- Fix: #653: Order of import statements in the Setup classes generated by dev:module:create (by Aad Mathijssen)
- Add: #628: --set-gtid-purged-off option for db:dump command (by Luke Rodgers)
- Add: #654: --add-strict-types option for dev:module:create command (by Aad Mathijssen)
- Add: #661: Magento 2.4.1 Open Source (by Christian Münch)
- Add: #665: --include option for db:dump command (by Hannes Drittler)
- Add: #666: --force option for db:import command (by Luke Rodgers)
- Fix: #651: Fix PSR-12-violation in the registration.php file generated by dev:module:create (by Aad Mathijssen)
- Fix: #631: Correct grammar on db:dump help text (by Dan Wallis)
- Imp: Updated Symfony dependencies (Dependabot)
- Imp: Updated psysh dependency (Dependabot)
- Imp: Updated phpunit dependencies (Dependabot)

4.3.0 (2020-09-18)
------------------

- New: #615: Add option to dump db with —no-tablespaces option (by Torben Höhn)
- Imp: Updated Symfony and Composer components to latest v4.4 (by dependabot)
- Add: #617: Gitleaks config (by Christian Münch)
- Add: Github super linter (by Christian Münch)
- Fix: #603: Fixed typos in help (by Rico Neitzel)
- Fix: #621: Correct list of 'dotmailer' tables (by Dan Wallis)

4.2.0 (2020-08-22)
------------------

- New: #598: #597 Add support for additional fields to customer:create (by Alexander Menk)
- New: #596: Support Magento 2.4.0 (by Christian Münch)
- New: #586: MySQL 8 Support (by Callum Atwal)
- New: #575: Env Checker (by Slawomir Boczek)
- New: #552: Support Magento 2.3.5 (by Alexander Menk)
- Imp: #551: When mysqldump fails, make db:dump fail as well (Pieter Hoste)
- New: #547: Add table groups 'oauth' and 'klarna', update README.md (by Timon de Groot)
- New: #544: --skip-magento-compatibility-check (by Timon de Groot)
- Imp: #568: Strip all dotmailer tables (by Arnoud Beekman)
- Imp: #548: Db console strip temp tables (by Doug Hatcher)
- Imp: #543: Check if config:env:set does any changes (by Timon de Groot)
- Imp: Updated Dependencies (Symfony latest 4.4.x) (by Christian Münch)
- Imp: Update phpunit to 8.x (by Christian Münch)
- Fix: #590: Fix format if db:status is dumped as CSV (by Christian Münch)

4.1.0 (2020-04-19)
------------------

- New: Three new commands (config:env:create, config:env:show, config:env:set) (by Peter Jaap Blaakmeer)
- Imp: Update Composer to 1.10.5
- Fix: #535: Twig 2.0 compatibility
- Imp: #538: Support Magento 2.3.4 (by Alexander Menk)
- Fix: #541: Sample data deploy fails on 4.0.4 version with Magento 2.3.4 (reported by easysoft-team)

4.0.4 (2020-03-09)
------------------

- Fix: #521: admin:user:create throws Exception (by Christian Münch)

4.0.3 (2020-02-01)
------------------

- Imp: Add tests for ConfigurationLoader (by Christian Münch)
- Fix: #525: n98-magerun for magento repository (reporter YevgenK)
- Fix: #523: --skip-root-check throws an error (reporter Tadeu Rodrigues)

4.0.2 (2020-01-03)
------------------

- Fix: #519: Fix loading `app/etc/n98-magerun.yaml` (by cmacdonald-au)
- Imp: #518: Improved README markdown syntax (by Jeroen Vermeulen)

4.0.1 (2020-01-02)
------------------

- Fix: Wrong integration:command description
- Fix: #517: Fatal error when /etc/n98-magerun2.yaml exists

4.0.0 (2019-12-30)
------------------

- New: New commands to handle webapi integrations (by Christian Münch)
- New: #374: Add "area" option to dev:console (by Christian Münch)
- New: #494: Add mycli support (by Christian Münch)
- Add: roave/security-advisories component for a better security
- Add: Magento 2.3.3 in installer (by Christian Münch)
- Add: #503: Captainhook suppport (by Christian Münch)
- Add: #501: Strip B2B quotes as part of the @quotes alias (by Dan Wallis)
- Imp: Match Magento Code Style (v2.2.10)
- Imp: Update Symfony Components to 4.4
- Imp: Update PHPUnit to 6.5
- Imp: Update php-cs-fixer to 2.16
- Imp: #504: Enable PHP 7.3 for travis builds (by Christian Münch)
- Imp: #492: Update readme (by operator888)
- Fix: Composer package naming (vfsStream)
- Fix: #488: mcrypt is optional module in Magento 2.3 (by Christian Münch)
- Fix: #487: Downgrade versions command broken (by Christian Münch)
- Fix: #490: Wildcard matching of db:dump command (by Dan Wallis)
- Fix: #244: Cron Job List command sys:cron:list does not merges system config path values
- Del: Removed official support for Magento 2.2 and Magento 2.1
- Del: Removed unsupported Magento versions from installer

3.2.0 (2019-07-19)
------------------

- Fix: #293: Admin password expires despite recent change (by Robbert Stevens)
- Imp: #481: Add table groups (by Timon de Groot)

3.1.0 (2019-06-29)
------------------

- Fix: #470: Sales stripped database dump does not exclude EE tables (by Rik Willems)
- Add: #475: Update travis versions (by Christian Münch)
- Add: #476: Update versions for installer (by Christian Münch)
- New: #466: Phar dist package (by Thomas Negeli)
- New: command admin:token:create (by Christian Münch)
- New: command customer:token:create (by Christian Münch)

3.0.10 (2019-06-10)
------------------

- Fix: db:dump returns exit code 0 on fail (by Christian Münch)
- Fix #458 - Password dialog is hidden

3.0.9 (2019-05-31)
------------------

- Fix: #439: Add 2fa tables (by Peter Jaap Blaakmeer)
- Fix: #453: Fix completely exclude tables with --exclude command (by Arnoud Beekman)
- Fix: #459: Update readme - Script command documentation fix (by Hardy Johnson)
- Fix: #460: Typo: unkown -> unknown (by Alexander Menk)
- Fix: #461: update twig/twig to version later than 1.37.1 (by wb-lime)

3.0.8 (2019-04-10)
------------------

- Fix #450: command sys:setup:compare-versions returns errors on a lot of core modules (thanks Emanuele Gian)

3.0.7 (2019-04-01)
------------------

- Fix for Composer autoloader / Magento 2.2.8 (thanks mystix, Rick Schippers)

3.0.6 (2019-03-27)
------------------

- Fix #442: Non standard conform strings in config.yaml (by Tom Klingenberg, thanks Paul Canning)

3.0.5 (2019-03-26)
------------------

- Fix #440: Add phar wrapper for Magento 2.3.1 compatibility (by Christian Münch)
- Fix #441: make build.sh OSX compatible (by Keith Bentrup)

3.0.4 (2019-02-04)
------------------

- Fix #425: Fixed reindex command syntax (by Simon Sprankel)
- Fix #428: Unused property (by Leandro F. L.)
- Fix #432: Add method used by newer symfony version (by AreDubya)

3.0.3 (2018-12-20)
------------------

- Fix: #422: Expansion of ~ in --root-dir option is no longer working (by Tjerk Ameel)

3.0.2 (2018-12-13)
------------------

- Fix: #299: config:get, fix --scope-id=0 filtering (by Jürgen Thelen)
- Fix: #308: cache:clean, add handling of invalid cache types (by Jürgen Thelen)
- Fix: #417: Prevent fatal error during Magento Init
- Fix: #414: Hide typed password in password change dialog

3.0.1 (2018-12-09)
------------------

- Fix: #413: sample:deploy PRE_COMMAND_RUN issue on Magento 2.2.6 (by JDavidVR)

3.0.0 (2018-12-07)
------------------

- Compatibility to Magento 2.3.0
- Refactoring of Application class
- Fix: #411 create customer store id
- Removed official support for Magento 2.0.x
- Removed support for PHP 5.6

2.3.3 (2018-11-17)
------------------

- Upgrades Travis Setup (check new Magento Versions) (by Christian Münch)
- Fix: #329: Area code not set failures (by Christian Münch)

2.3.2 (2018-11-07)
------------------

- Fix for #407 phar mismatch after self-update (by Christian Münch)
- Uses Magento's DirectoryList to get config folder path (by Manuele Menozz)

2.3.1 (2018-10-18)
------------------

- --stdout broken in 2.3.0 (by Peter Jaap Blaakmeer / Christian Münch)

2.3.0 (2018-10-13)
------------------

- Add CE 2.2.6 and 2.1.15 (by Bono de Visser)
- Add CE 2.1.14, 2.2.5 (by Marc)
- Add to the stripped db dump command the dotmailer group with email sensitive data (by Calin Dumitrescu)
- Additional enterprise-only tables missing from the table group configuration (by Matthew O'Loughlin)
- Emulation-Mode not needed after set current area. This resolves #245 on empty cache (by Julian Wundrak)
- Homebrew php tap is deprecated (by Anton Evers)
- Updated docs (by kolaente)
- Implemented option to create git friendly dumps / resolves #386 (by kolaente)
- Update readme with dotmailer group (by Calin Dumitrescu)
- Update README (by Daniël van der Linden)
- Strip company_* tables for the Commerce B2B extension when using @customers (by Daniël van der Linden)
- Update readme.rst (by Leandro F. L)
- Fix: #377 sys:cron:run - translation is not loaded (by Christian Münch)
- Fix: #381 Area code not set (by Christian Münch)
- Fix: #302 Replace n98-magerun.phar witg n98-magerun2.phar Christian Münch)
- Fix: #388 Add correct default for --add-time option (by Christian Münch)

2.2.0 (2018-06-10)
------------------

- Add --dry-run option to self-update command (by Christian Münch)
- Added downloads: CE 2.1.12, 2.1.13, 2.2.3 & 2.2.4 (by Marc)
- Fix db:dump "@sessions" table group (by Tjerk Ameel)
- Fix: db:dump fixed table-group @customer (by Matthias Herold)
- Cache type parameter documentation for cache:flush (by Sebastian Lechte)
- Add types parameter to cache:flush (like on cache:clean) (by Jonas Huenig)
- Correct --forceUseDb help description (by Stephan Hochdörfer)
- Remove used use statement as reported by php-cs-fixer (by Stephan Hochdörfer)
- Add option to force Composer to use same PHP binary (by Stephan Hochdörfer)
- cache:clean > fixing help message (by Rafael Corrêa Gomes)
- Fix issue #333 - dev:template-hints not working with Magento 2.2.0 (by Christian Münch)
- Added additional tables to "search" table-group (by Erfan)
- Reverted composer/composer constraint to v1.0 (by Pieter Hoste)
- Unstripping "authorization*" in db:dump (by Edward Simpson)
- Fix grammar in readme by (Erik Hansen)

2.1.2 (2018-01-29)
------------------

* Update composer dependencies

2.1.1 (2018-01-28)
------------------

* Upgrade embedded composer package
* Fix: Fix "area code not set" error message in customer commands (by Christian Münch)
* Fix wrong headlines in cutomer:list command (by Christian Münch)

2.1.0 (2018-01-28)
------------------

* Imp: Polish code - Comvert array syntax and corrected docblocks (by Christian Münch)
* Fix: Fixed running cronjobs configured in database, scheduled imports for example (by Johan Spoelstra)
* New: Add customer:change-password command (by Christian Münch)
* Imp: Add requirements for Magento 2.2 / make:module in dev:console (by Christian Münch)
* Fix: Fix version for Magento CE 2.1.10 download (by Mystix)
* Fix: Ignore the public folder when gathering the magerun scripts (by Mark Simon)
* Imp: Add 2.2.1 + 2.1.10 to install command (by Alexander Menk, #335)
* Imp: Add Magento-Root to sys:info command (by Christian Münch)
* Imp: Magento keys are now found in Magento Marketplace (by Jonas Hünig. #325)
* Fix: Fix bash autocompletion, fixes (Tom Klingenberg, Floddy, #331)

2.0.0 (2017-10-16)
------------------

* Major Break: config:get, config:set, config:delete commands are renamed
  -> config:store:get, config:store:set, config:store:delete
  (by Christian Münch)
* New: Command eav:attribute:remove (by Jürgen Thelen, #307)
* Imp: Strip admin tables (by Max Chadwick, #309)
* Imp: Updated Magento releases (by Kristof Ringleff, #311)
* Imp: More config values for install command (by Manuele Menozzi, #312)
* New: Command media:dump (by Elias Kotlyar, #319)
* Fix: Undefined index during cronjob execution (by Anton Evers, #201)
* Imp: Code imrovements (by Tom Klingenberg)
* Fix: Magento 2.2 Compatibility (by Christian Münch)

1.6.0 (2017-08-26)
------------------

* Feature: Add Magento Open Source Edition 2.1.8 (by Alexander Menk)
* Feature: App state injection support in sys:check command (by Manuel Schmid / Christian Münch)
* Feature: Improve db:dump command (by Scott Buchanan, #303 #304)
* Fix: GMT timestamps for Magento 2.2.0 (by Tom Klingenberg, #296)
* Fix: Typo in install command (by Tom Klingenberg / Tim Neutken, #297)
* Fix: Typo in readme (Chris Orlando, #301)

1.5.0 (2017-06-08)
------------------

* Fix: Add Phar checksum guide to readme (report by Max Chadwick, fix by Tom Klingenberg, #279)
* Fix: Prevent exceptions in dev:console from being suppressed and hidden (by Jason Woods, #282)
* Fix: Sync optimize() with Magerun1 (by Alexander Menk, #291)
* Fix: Updates script command to have non-zero exit code (by Christian Münch)
* Fix: customer:create shows incorrect notice (by Christian Münch, #289)
* Imp: Streamline db:dump with Magerun (by Tom Klingenberg)
* Imp: Optimize description of --add-time option (by Christian Münch, #281)
* New: Add current Magento2 versions (by Kristof Ringleff, #292)
* New: Port db:dump --exclude from Magerun 1 (by Torrey Tsui, #294)
* New: Command index:trigger:recreate (by Christian Münch)

1.4.0 (2017-03-30)
------------------

* Fix: sys:cron:schedule 'area code is not set' exception. (by Pieter Hoste, #277)
* Fix: Allow -1 as value for infinite max nesting level for xdebug (by Peter Jaap Blaakmeer, #278)
* Fix: Generate a shorter version of registration.php (by Alexander Turiak #280)
* New: Command eav:attribute:list (by Jürgen Thelen, #99)
* New: Command dev:asset:clear (by Jürgen Thelen, #141)
* New: Command config:data:acl (by Christian Münch)
* New: Command config:data:di (by Christian Münch)
* New: Command search:engine:list (by Christian Münch)

1.3.3 (2017-03-03)
------------------

* Fix: Set forceUseDb option to type VALUE_NONE (by Juan Alonso, #273)
* Fix: install Magento2 without development dependencies (by Tom Klingenberg, #272)
* Fix: db:dump stdout output (report by Flip Hess, fix by Tom Klingenberg, #258)
* Imp: composer.json templates (by Christian Münch, Tom Klingenberg)
* New: Port of cache:view (by Jürgen Thelen, #40)
* New: Port of cache:report (by Jürgen Thelen, #39)
* New: Add current Magento2 version 2.1.5 (by Tom Klingenberg)

1.3.2 (2017-02-13)
------------------

* Fix: Wrong version identifiers 2.0.8 - 2.0.10 (by Tom Klingenberg, #229)

1.3.1 (2017-02-12)
------------------

* Fix: Install command using wrong php binary and eating installer errors (report by David Lambauer, fix by Tom Klingenberg, #267)
* Fix: Minor PHP version for Magento 2 extensions (by Alexander Turiak, #269)
* Fix: Magento object manager usage in production mode (by Tom Klingenberg, #241)
* Fix: Support for db-setting arrays (e.g. driver_options) (by Tom Klingenberg)
* Fix: Class names in data setup twig template (by Jurgisl, #262)
* Fix: Regex in VariablesCommandTest (by Jürgen Thelen, #255)
* Imp: Build phar reproduceable and from dev requirements (by Tom Klingenberg)
* Imp: Support NULL values in config:set and config:get (by Tom Klingenberg, #208)
* Imp: Better handle incomplete Magento 2 installments (by Tom Klingenberg)
* Imp: Dispatch adminhtml_cache_flush_all with cache:flush (report by Viktor Steinwand, #263)
* New: Compilation of the bash autocomplete-file (by Tom Klingenberg)
* New: Add current Magento2 versions (thanks Pieter Hoste, #270)
* New: Add current Magento2 versions (by Tom Klingenberg)
* New: Add sys:cron:schedule command (by Pieter Hoste, #257)
* New: Port of design:demo-notice command (by Jürgen Thelen, #69)
* New: Build with PHP 7.1 for some jobs (by Tom Klingenberg, #256)
* New: Port of admin:notifications command (by Jürgen Thelen, #29)

1.3.0 (2016-12-01)
------------------

* Fix: Fatal error when running Magerun 2 inside a Magento 1 tree (by Tom KLingenberg, #253)
* Fix: Add missing areas to the observer list (by Pieter Hoste, #249)
* Fix: Do not drop all sales_order_status* tables (report by Brent Jameson, fix by Tom KLingenberg, #239)
* Fix: Prevent Mysql deadlock on admin password change (by Tom KLingenberg, #242)
* New: Add Magento CE 2.1.2 (by Raul E Watson, #252)
* New: Debug output on --skip-root-check option (by Tom Klingenberg)
* New: Interactive console: Support for initial code argument (by Christian Münch)
* New: Introduced test framework (by Christian Münch)

1.2.2 (2016-09-12)
------------------

* Fix: Prevent hang on Travis (by Christian Münch, #238)
* Fix: Script repository is the same as Magerun 1 (report by Jeroen Bleijenberg, #235)
* New: Add Magento CE 2.0.7 and 2.1.1 (by Raul E Watson, #237)
* New: PHP-CS-Fixer integration (by Tom Klingenberg)

1.2.1 (2016-08-24)
------------------

* Fix: Build fixes and release to test continuous build on files.magerun.net (by Tom Klingenberg)

1.2.0 (2016-08-24)
------------------

* Fix: Fatal error in Phpstorm .idea folder detection for path in dev:urn-catalog:generate (by Tom Klingenberg, #233)
* Fix: Wrong template-hints config value (by Tommy Pyatt, #230)
* Fix: Broken scope-id detection (by Christian Münch)
* Imp: Pass along return value in db:query command (by Tom Klingenberg)
* Imp: Version constraints for Symfony console and Magento 2.1 (report by Pieter Hoste, #234)
* New: Add sys:setup:downgrade-versions command (by Tom Klingenberg)
* New: Add customer:create command (by Christian Münch, #54)
* New: Add code generator features to dev:console command (by Chrπistian Münch)

1.1.17 (2016-07-18)
------

* Fix: Posix conform --root-dir parsing (report by Andreas Lutro, fix by Tom Klingenberg, #224)
* Fix: Fix sys:maintenance --on and --off options (report by Rob Egginton, fix by Tom Klingenberg, #211)
* Fix: Replace remaining instances of Magento 1 local.xml with Magento 2 env.php (by Matthew O'Loughlin, #207)
* Feature: Install command: magento-ce-2.1.0 version (by Tom Klingenberg, #223)
* New: Automatically detect Phpstorm .idea folder for path in dev:urn-catalog:generate (by Tom Klingenberg)

1.1.16 (2016-06-30)
-------------------

Fix: wrong version in Application.php

1.1.15 (2016-06-28)
-------------------

* Fix: Magento 2.1 version compatibility (by p-makowski, #214)
* Fix: help description of system:setup:compare-versions (by p-makowski, #214)
* Fix: PHP version requirements in documentation (report by Carsten Bohuslav, #204)
* Fix: Install command use-default-config-params option (fix by Tom Klingenberg)
* Fix: Install command replace-htaccess-file option (report by Matthias Zeis, fix by Tom Klingenberg, #191)
* Fix: Fix undefined index access in cron:list (report by redboxmarcins, fix by Tom Klingenberg, #201)
* Imp: Build with timestamp alignment and in clean directory (by Tom Klingenberg)
* New: Add Homebrew installation (by Matthéo Geoffray, #203)

1.1.14 (2016-05-29)
-------------------

* Fix: Regression test for #199 (report by Pieter Hoste, fix by Tom Klingenberg, #200)
* Fix: Travis build exited too early (by Tom Klingenberg)
* Feature: Install command: magento-ce-2.0.7 version (by Raul E Watson, #202)

1.1.13 (2016-05-22)
-------------------

* Fix: Fix db:dump regression in 1.1.12 (report by Pieter Hoste, fix by Tom Klingenberg, #199)
* Imp: Check repository connectivity in Travis build (by Tom Klingenberg)

1.1.12 (2016-05-22)
-------------------

* Fix: Fix open command detection (by Tom Klingenberg)
* Fix: Wrong version display in sys:setup:compare-versions (by Tom Klingenberg)
* Fix: Install command regression handling download errors in 1.1.11 (by Tom Klingenberg)
* Fix: Align Symfony Console version requirements with Magento 2 (by Tom Klingenberg, #198)

1.1.11 (2016-05-18)
-------------------

* Fix: Cron app state emulation (by Sam Tay, #196)
* Fix: Install command missing PHP extension checks of mbstring and zip (by Tom Klingenberg)
* Feature: Install command: magento-ce-2.0.6 version (by Raul E Watson, #197)

1.1.10 (2016-05-06)
-------------------

- Fix: db:console password parameter name (by Federico Rivollier)

1.1.9 (2016-05-05)
------------------

* Fix: Build script not stop asking (by Christian Muench)
* Fix: Bump version to build again (by Tom Klingenberg)

1.1.8 (2016-05-05)
------------------

- No code changes. Tag created for release management only.

1.1.7 (2016-04-30)
------------------

- Feature: Add install-sample-data sub-command
- Feature: Add zsh autocompletion
- Feature: Add trailing namespace slash for Packagist compatibility
- Feature: Add .github pull request template
- Feature: Add autocompletion for zsh
- Feature: Add Magento CE 2.0.3 and 2.0.5
- Fix: Check for optional replaceHtaccessFile parameter (fixes #191)
- Fix: Allow crons to run from __call
- Fix: Remove Magento CE 2.0.3
- Fix: Preserve closing tag on PSR-4 namespace prefixes
- Fix: Prevent fatal error on command creation
- Fix: Stabilize composer
- Fix: Show command name next to class name when add
- Fix: Streamline with install-sample-data sub-command
- Imp: Extract run magento command method
- Imp: Extract run magento command method
- Imp: Move fish file to its own directory
- Imp: Streamline
- Imp: Track changes
- Imp: Exit early, do strict comparisons
- Imp: Merge pull requests and code cleanups

1.1.6 (2016-04-08)
------------------

- No code changes. Tag created for release management only.

1.1.5 (2016-04-05)
------------------

- Feature: Add fish shell autocompletion
- Feature: Add Magento CE 2.0.4
- Fix: Fix config:get script output value encoding
- Fix: Code-style fixes (PSR2 length)
- Fix: Bugfix in reinit to prevent Config already initialized
- Fix: Base-URL check on IP addresses (#172)
- Fix: Whitespace and code style
- Fix: __halt_compiler keyword case
- Fix: Custom-file-name-property
- Fix: Global options swallowing arguments
- Imp: Extract ConfigurationLoader subcomponents
- Imp: Extract bootstrap class
- Imp: Streamline Application
- Imp: Add array typehints for config parameter (#795)
- Imp: Move dispatcher init up
- Imp: Remove debug cruft
- Imp: Cleanup test fixtures and code
- Imp: Update composer properties
- Imp: Update changelog

1.1.4 (2016-01-30)
------------------

- Feature: Add eav:attribute:view command
- Feature: Add sys:setup:change-version command
- Fix: Download links for n98-magerun.phar
- Fix: Typo in environment variable name
- Fix: Code style and typehints
- Fix: Minor CS improvements
- Imp: Update changelog
- Imp: Add additional debug code to log registered core and internal commands
- Imp: Update MySQL 5.6 setup on Travis
- Imp: Require sudo on Travis
- Imp: Make Travis fail early
- Imp: Rename @type to @var in code style

1.1.3 (2015-12-30)
------------------

- No code changes. Tag created for release management only.

1.1.2 (2015-12-30)
------------------

- No code changes. Tag created for release management only.

1.1.1 (2015-12-26)
------------------

- No code changes. Tag created for release management only.

1.1.0 (2015-12-26)
------------------

- No code changes. Tag created for release management only.

1.0.0 (2015-11-18)
------------------

- No code changes. Tag created for release management only.
