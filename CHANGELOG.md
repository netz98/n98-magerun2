RECENT CHANGES
==============

7.1.0-dev
---------

- Add: New commands to manage sales sequences (by Jeroen Boersma)
- Add: New command to redeploy base packages (by Christian Münch)
- Imp: Add debug output if Magento Core Commands cannot be used (by Christian Münch)

7.0.3
-----

- Fix: #1164: Magento Core Commands cannot be loaded. Please verify if "bin/magento" is running (by Christian Münch)

7.0.2
-----
 
- Fix: #1164: Magento Core Commands cannot be loaded. Please verify if "bin/magento" is running
- Imp: Update 3rd party dependencies (captainhook, phpstan, phpunit, psysh)

7.0.1
-----

- Fix: #447: Memory usage within Magerun script (by Christian Münch)
- Fix: #1144: Allow to add instead of replace a PSR-4 namespace (by Christian Münch)
- Fix: #1147: Fix command return value and add phar test (by Christian Münch)
- Fix: #1149: Use defined PHP binary for calls to bin/magento (by Christian Münch)
- Imp: Update 3rd party dependencies (captainhook, psysh, php-cs-fixer, phpstan, phpunit)

7.0.0
-----

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

6.1.1
-----

- Fix: #1066: broken index:list command (by Christian Münch)
- Imp: Update 3rd party dependencies (Symfony, psysh, captainhook, dydev-dot-access-data, phpunit, twig)

6.1.0
-----

- Add: Magento 2.4.5-p1 / 2.4.4-p2 (by Simon Sprankel) 
- Imp: Update 3rd party dependencies (phpstan, requests library)
- Fix: #969: Mark cron as executed (by Pieter Hoste)
- Del: Magento 2.3.7 in ddev test setup (by Christian Münch)

6.0.1
-----

- Fix: Update twig (security fix)

6.0.0
-----

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

5.2.0
-----

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
- Fix: #998: Pass empty string as default value to getFileName (by Peter Jaap)
- Fix: #1015: deprecated: passing null to dirname (by Alexander Menk).
- Fix: #1019: type error in cache-list command on php 8.1 (by Tom Klingenberg)
- Fix: #1024: config:store:get wrong filters applied (by Alexander Dite)
- Fix: typos and wrong infos in help text (by Christian Münch)

5.1.0
-----

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

5.0.2
-----

- Fix: #966: InstalledVersions.php could not be opened

5.0.1
-----

- Fix: #964: Broken with guzzle dependency chain

5.0.0
-----

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

4.9.1
-----

- Fix: #901: dev:console command outputs "must be compatible with" error (by Mark Shust)


4.9.0
-----

- Add: #899: config:env:delete command (by Pieter Hoste) 
- Add: Dependency information in README (by Christian Münch)
- Imp: Updated dependencies (faker, psysh, symfony-console-autocomplete, php-cs-fixer, captainhook)
- Fix: #898: Disable Composer gc during Magerun process (by Christian Münch)

4.8.0
-----

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

4.7.0
-----

- New: Command db:add-default-authorization-entries (by Christian Münch)
- Imp: Add handling for missing authorization rule/role in db:import (by Christian Münch, Alexander Menk)
- Add: Add table authorization_role to table group @admin (by hannes011)
- Fix: #781 - empty cron expression (by Christian Münch)
- Fix: #789 - sampledata:deploy returns composer error, bin/magento works (by Christian Münch)

4.6.1
-----

- Fix: broken self-update command (by Christian Münch)

4.6.0
-----

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

4.5.0
-----

- Add: Magento 2.4.2 (by Christian Münch)
- Imp: Change autoloading from PSR-0 to PSR-4 (by Tom Klingenberg)
- Imp: Updated n98/junit-xml dependency (by Tom Klingenberg)
- Imp: Updated Symfony dependencies (Dependabot)
- Imp: Updated faker dependency (Dependabot)
- Imp: Updated captainhook dependency (Dependabot)
- Imp: Updated php-cs-fixer dependency (Dependabot)
- Imp: Test with PHP 7.4 in github actions (by Christian Münch)
- Fix: When the mysql import fails, make db:import fail as well (by Pieter Hoste)
- Fix: Set php version to 7.4 in Github actions (by Christian Münch)
- Fix: db:dump set correct default value (by Torben Höhn)

4.4.0
-----

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

4.3.0
-----

- New: #615: Add option to dump db with —no-tablespaces option (by Torben Höhn)
- Imp: Updated Symfony and Composer components to latest v4.4 (by dependabot)
- Add: #617: Gitleaks config (by Christian Münch)
- Add: Github super linter (by Christian Münch)
- Fix: #603: Fixed typos in help (by Rico Neitzel)
- Fix: #621: Correct list of 'dotmailer' tables (by Dan Wallis)

4.2.0
-----

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

4.1.0
-----

- New: Three new commands (config:env:create, config:env:show, config:env:set) (by Peter Jaap Blaakmeer)
- Imp: Update Composer to 1.10.5
- Fix: #535: Twig 2.0 compatibility
- Imp: #538: Support Magento 2.3.4 (by Alexander Menk)
- Fix: #541: Sample data deploy fails on 4.0.4 version with Magento 2.3.4 (reported by easysoft-team)

4.0.4
-----

- Fix: #521: admin:user:create throws Exception (by Christian Münch)

4.0.3
-----

- Imp: Add tests for ConfigurationLoader (by Christian Münch)
- Fix: #525: n98-magerun for magento repository (reporter YevgenK)
- Fix: #523: --skip-root-check throws an error (reporter Tadeu Rodrigues)

4.0.2
-----

- Fix: #519: Fix loading `app/etc/n98-magerun.yaml` (by cmacdonald-au)
- Imp: #518: Improved README markdown syntax (by Jeroen Vermeulen)

4.0.1
-----

- Fix: Wrong integration:command description
- Fix: #517: Fatal error when /etc/n98-magerun2.yaml exists

4.0.0
-----

- New: New commands to handle webapi integrations (by Christian Münch)
- New: #374: Add "area" option to dev:console (by Christian Münch)
- New: #494: Add mycli support (by Christian Münch)
- Add: roave/security-advisories component for a better security
- Add: Magento 2.3.3 in installer (by Christian Münch)
- Add: #503: Captainhook suppport (by Christian Münch)
- Add: #501: Strip B2B quotes as part of @quotes alias (by Dan Wallis)
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

3.2.0
-----

- Fix: #293: Admin password expires despite recent change (by Robbert Stevens)
- Imp: #481: Add table groups (by Timon de Groot)

3.1.0
-----

- Fix: #470: Sales stripped database dump does not exclude EE tables (by Rik Willems)
- Add: #475: Update travis versions (by Christian Münch)
- Add: #476: Update versions for installer (by Christian Münch)
- New: #466: Phar dist package (by Thomas Negeli)
- New: command admin:token:create (by Christian Münch)
- New: command customer:token:create (by Christian Münch)

3.0.10
------

- Fix: db:dump returns exit code 0 on fail (by Christian Münch)
- Fix #458 - Password dialog is hidden

3.0.9
-----

- Fix: #439: Add 2fa tables (by Peter Jaap)
- Fix: #453: Fix completely exclude tables with --exclude command (by Arnoud Beekman)
- Fix: #459: Update readme - Script command documentation fix (by Hardy Johnson)
- Fix: #460: Typo: unkown -> unknown (by Alexander Menk)
- Fix: #461: update twig/twig to version later than 1.37.1 (by wb-lime)

3.0.8
-----

- Fix #450: command sys:setup:compare-versions returns errors on a lot of core modules (thanks Emanuele Gian)

3.0.7
-----

- Fix for Composer autoloader / Magento 2.2.8 (thanks mystix, Rick Schippers)

3.0.6
-----

- Fix #442: Non standard conform strings in config.yaml (by Tom Klingenberg, thanks Paul Canning)

3.0.5
-----

- Fix #440: Add phar wrapper for Magento 2.3.1 compatibility (by Christian Münch)
- Fix #441: make build.sh OSX compatible (by Keith Bentrup)

3.0.4
-----

- Fix #425: Fixed reindex command syntax (by Simon Sprankel)
- Fix #428: Unused property (by Leandro F. L.)
- Fix #432: Add method used by newer symfony version (by AreDubya)

3.0.3
-----

- Fix: #422: Expansion of ~ in --root-dir option is no longer working (by Tjerk Ameel)

3.0.2
-----

- Fix: #299: config:get, fix --scope-id=0 filtering (by Jürgen Thelen)
- Fix: #308: cache:clean, add handling of invalid cache types (by Jürgen Thelen)
- Fix: #417: Prevent fatal error during Magento Init
- Fix: #414: Hide typed password in password change dialog

3.0.1
-----

- Fix: #413: sample:deploy PRE_COMMAND_RUN issue on Magento 2.2.6 (by JDavidVR)

3.0.0
-----

- Compatibility to Magento 2.3.0
- Refactoring of Application class
- Fix: #411 create customer store id
- Removed official support for Magento 2.0.x
- Removed support for PHP 5.6

2.3.3
-----

- Upgrades Travis Setup (check new Magento Versions) (by Christian Münch)
- Fix: #329: Area code not set failures (by Christian Münch)

2.3.2
-----

- Fix for #407 phar mismatch after self-update (by Christian Münch)
- Uses Magento's DirectoryList to get config folder path (by Manuele Menozz)

2.3.1
-----

- --stdout broken in 2.3.0 (by Peter Jaap / Christian Münch)

2.3.0
-----

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

2.2.0
-----

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

2.1.2
-----

* Update composer dependencies

2.1.1
-----

* Upgrade embedded composer package
* Fix: Fix "area code not set" error message in customer commands (by Christian Münch)
* Fix wrong headlines in cutomer:list command (by Christian Münch)

2.1.0
-----

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

2.0.0
-----

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

1.6.0
-----

* Feature: Add Magento Open Source Edition 2.1.8 (by Alexander Menk)
* Feature: App state injection support in sys:check command (by Manuel Schmid / Christian Münch)
* Feature: Improve db:dump command (by Scott Buchanan, #303 #304)
* Fix: GMT timestamps for Magento 2.2.0 (by Tom Klingenberg, #296)
* Fix: Typo in install command (by Tom Klingenberg / Tim Neutken, #297)
* Fix: Typo in readme (Chris Orlando, #301)

1.5.0
-----

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

1.4.0
-----

* Fix: sys:cron:schedule 'area code is not set' exception. (by Pieter Hoste, #277)
* Fix: Allow -1 as value for infinite max nesting level for xdebug (by Peter Jaap, #278)
* Fix: Generate a shorter version of registration.php (by Alexander Turiak #280)
* New: Command eav:attribute:list (by Jürgen Thelen, #99)
* New: Command dev:asset:clear (by Jürgen Thelen, #141)
* New: Command config:data:acl (by Christian Münch)
* New: Command config:data:di (by Christian Münch)
* New: Command search:engine:list (by Christian Münch)

1.3.3
-----

* Fix: Set forceUseDb option to type VALUE_NONE (by Juan Alonso, #273)
* Fix: install Magento2 without development dependencies (by Tom Klingenberg, #272)
* Fix: db:dump stdout output (report by Flip Hess, fix by Tom Klingenberg, #258)
* Imp: composer.json templates (by Christian Münch, Tom Klingenberg)
* New: Port of cache:view (by Jürgen Thelen, #40)
* New: Port of cache:report (by Jürgen Thelen, #39)
* New: Add current Magento2 version 2.1.5 (by Tom Klingenberg)

1.3.2
-----

* Fix: Wrong version identifiers 2.0.8 - 2.0.10 (by Tom Klingenberg, #229)

1.3.1
-----

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

1.3.0
-----

* Fix: Fatal error when running Magerun 2 inside a Magento 1 tree (by Tom KLingenberg, #253)
* Fix: Add missing areas to the observer list (by Pieter Hoste, #249)
* Fix: Do not drop all sales_order_status* tables (report by Brent Jameson, fix by Tom KLingenberg, #239)
* Fix: Prevent Mysql deadlock on admin password change (by Tom KLingenberg, #242)
* New: Add Magento CE 2.1.2 (by Raul E Watson, #252)
* New: Debug output on --skip-root-check option (by Tom Klingenberg)
* New: Interactive console: Support for initial code argument (by Christian Münch)
* New: Introduced test framework (by Christian Münch)

1.2.2
-----

* Fix: Prevent hang on Travis (by Christian Münch, #238)
* Fix: Script repository is the same as Magerun 1 (report by Jeroen Bleijenberg, #235)
* New: Add Magento CE 2.0.7 and 2.1.1 (by Raul E Watson, #237)
* New: PHP-CS-Fixer integration (by Tom Klingenberg)

1.2.1
-----

* Fix: Build fixes and release to test continuous build on files.magerun.net (by Tom Klingenberg)

1.2.0
-----

* Fix: Fatal error in Phpstorm .idea folder detection for path in dev:urn-catalog:generate (by Tom Klingenberg, #233)
* Fix: Wrong template-hints config value (by Tommy Pyatt, #230)
* Fix: Broken scope-id detection (by Christian Münch)
* Imp: Pass along return value in db:query command (by Tom Klingenberg)
* Imp: Version constraints for Symfony console and Magento 2.1 (report by Pieter Hoste, #234)
* New: Add sys:setup:downgrade-versions command (by Tom Klingenberg)
* New: Add customer:create command (by Christian Münch, #54)
* New: Add code generator features to dev:console command (by Chrπistian Münch)

1.1.17
------

* Fix: Posix conform --root-dir parsing (report by Andreas Lutro, fix by Tom Klingenberg, #224)
* Fix: Fix sys:maintenance --on and --off options (report by Rob Egginton, fix by Tom Klingenberg, #211)
* Fix: Replace remaining instances of Magento 1 local.xml with Magento 2 env.php (by Matthew O'Loughlin, #207)
* Feature: Install command: magento-ce-2.1.0 version (by Tom Klingenberg, #223)
* New: Automatically detect Phpstorm .idea folder for path in dev:urn-catalog:generate (by Tom Klingenberg)

1.1.16
------

Fix: wrong version in Application.php

1.1.15
------

* Fix: Magento 2.1 version compatibility (by p-makowski, #214)
* Fix: help description of system:setup:compare-versions (by p-makowski, #214)
* Fix: PHP version requirements in documentation (report by Carsten Bohuslav, #204)
* Fix: Install command use-default-config-params option (fix by Tom Klingenberg)
* Fix: Install command replace-htaccess-file option (report by Matthias Zeis, fix by Tom Klingenberg, #191)
* Fix: Fix undefined index access in cron:list (report by redboxmarcins, fix by Tom Klingenberg, #201)
* Imp: Build with timestamp alignment and in clean directory (by Tom Klingenberg)
* New: Add Homebrew installation (by Matthéo Geoffray, #203)

1.1.14
------

* Fix: Regression test for #199 (report by Pieter Hoste, fix by Tom Klingenberg, #200)
* Fix: Travis build exited too early (by Tom Klingenberg)
* Feature: Install command: magento-ce-2.0.7 version (by Raul E Watson, #202)

1.1.13
------

* Fix: Fix db:dump regression in 1.1.12 (report by Pieter Hoste, fix by Tom Klingenberg, #199)
* Imp: Check repository connectivity in Travis build (by Tom Klingenberg)

1.1.12
------

* Fix: Fix open command detection (by Tom Klingenberg)
* Fix: Wrong version display in sys:setup:compare-versions (by Tom Klingenberg)
* Fix: Install command regression handling download errors in 1.1.11 (by Tom Klingenberg)
* Fix: Align Symfony Console version requirements with Magento 2 (by Tom Klingenberg, #198)

1.1.11
------

* Fix: Cron app state emulation (by Sam Tay, #196)
* Fix: Install command missing PHP extension checks of mbstring and zip (by Tom Klingenberg)
* Feature: Install command: magento-ce-2.0.6 version (by Raul E Watson, #197)

1.1.10
------

* Fix: db:console password parameter name (by Federico Rivollier)

1.1.9
-----

* Fix: Build script not stop asking (by Christian Muench)
* Fix: Bump version to build again (by Tom Klingenberg)

1.1.8
-----

* Update: Composer to 1.0.3

1.1.7
-----

* Fix: Check for optional replace-htaccess-file parameter (report by Matthias Zeis, fix by Tom Klingenberg, #191)
* Fix: Add trailing namespace prefix slash (by Phillip Jackson, #190)
* Update: Stabilize composer ^1.0.0 (by Tom Klingenberg)
* Feature: Add zsh auto-completion (by Sam Tay, #189)
* Feature: Install command: magento-ce-2.0.5 version (by Raul E Watson, #193)

1.1.6
-----

* Fix: Fix module loader (report by Matthias Walter, fix by Tom Klingenberg)

1.1.5
-----

* Fix: #172 Base-URL check on IP addresses (by Tom Klingenberg)
* Fix: Whitespace and code-style (by Tom Klingenberg)
* Feature: Install command: magento-ce-2.0.4 version (by Tom Klingenberg)
* Feature: Extract config-loader (by Tom Klingenberg)
* Feature: Add dry-run mode for db:dump (by Tom Klingenberg)

1.1.4
-----

* Feature: Install command: magento-ce-2.0.1 and magento-ce-2.0.2 versions (by Tom Klingenberg)
* Feature: #101 Porting command: eav:attribute:view (by Robbie Averill)
* Feature: #120 Porting command: sys:setup:change-version (by Robbie Averill)

1.1.3
-----

* Fix: #168 Version mismatch (by Tom Klingenberg)

1.1.2
-----

* Fix: #168 Version mismatch (by Tom Klingenberg)

1.1.1
-----

* Fix: #160 Stopfile broken (by Tom Klingenberg)
* Fix: #157 Undefined index moduleFolder (by Robbie Averill)
* Feature: #132 Porting command: giftcard:create (by Robbie Averill and Steve Robbins)
* Feature: #133 Porting command: giftcard:info (by Robbie Averill and Steve Robbins)
* Feature: #134 Porting command: giftcard:remove (by Robbie Averill and Steve Robbins)
* Feature: Added composer auth to download magento. (by Christian Münch)

1.0.0
-----

The first n98-magerun2 stable release to power the next-generation
open source digital commerce platform, Magento 2.0.

---

References
----------

* Visit our blog: <https://magerun.net>
