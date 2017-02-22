RECENT CHANGES
==============

1.3.3
-----
* Fix: install Magento2 without development dependencies (by Tom Klingenberg, #272)
* Fix: db:dump stdout output (report by Flip Hess, fix by Tom Klingenberg, #258)

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
