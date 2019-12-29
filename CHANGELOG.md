RECENT CHANGES
==============

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

* Visit our blog: http://magerun.net
