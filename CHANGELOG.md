RECENT CHANGES
==============

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
