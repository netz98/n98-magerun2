#!/bin/bash
# Installation:
#  Copy to /etc/bash_completion.d/n98-magerun.phar
# or
#  Append to ~/.bash_completion
# open new or restart existing shell session


_n98-magerun2()
{
    local state com cur

    cur=${words[${#words[@]}]}

    # lookup for command
    for word in ${words[@]:1}; do
        if [[ $word != -* ]]; then
            com=$word
            break
        fi
    done

    if [[ ${cur} == --* ]]; then
        state="option"
        opts=("--help:Display this help message" "--quiet:Do not output any message" "--verbose:Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug" "--version:Display this application version" "--ansi:Force ANSI output" "--no-ansi:Disable ANSI output" "--no-interaction:Do not ask any interactive question" "--root-dir:Force magento root dir. No auto detection" "--skip-config:Do not load any custom config." "--skip-root-check:Do not check if n98-magerun runs as root" "--skip-core-commands:Do not include Magento 2 core commands")
    elif [[ $cur == $com ]]; then
        state="command"
        coms=("help:Displays help for a command" "install:Install magento" "list:Lists commands" "open-browser:Open current project in browser \(experimental\)" "script:Runs multiple n98-magerun commands" "shell:Runs n98-magerun as shell" "admin\:notifications:Toggles admin notifications" "admin\:user\:change-password:Changes the password of a adminhtml user." "admin\:user\:create:Creates an administrator" "admin\:user\:delete:Delete the account of a adminhtml user." "admin\:user\:list:List admin users." "admin\:user\:unlock:Unlock Admin Account" "app\:config\:dump:Create dump of application" "cache\:clean:Clean magento cache" "cache\:disable:Disables Magento caches" "cache\:enable:Enables Magento caches" "cache\:flush:Flush magento cache storage" "cache\:list:Lists all magento caches" "cache\:report:View inside the cache" "cache\:status:Checks cache status" "cache\:view:Prints a cache entry" "catalog\:images\:resize:Creates resized product images" "catalog\:product\:attributes\:cleanup:Removes unused product attributes." "config\:data\:acl:Prints acl.xml data as table" "config\:data\:di:Dump dependency injection config" "config\:store\:delete:Deletes a store config item" "config\:store\:get:Get a store config item" "config\:store\:set:Set a store config item" "cron\:run:Runs jobs by schedule" "customer\:create:Creates a new customer/user for shop frontend." "customer\:hash\:upgrade:Upgrade customer\'s hash according to the latest algorithm" "customer\:info:Loads basic customer info by email address." "customer\:list:Lists all magento customers" "db\:console:Opens mysql client by database config from env.php" "db\:create:Create currently configured database" "db\:drop:Drop current database" "db\:dump:Dumps database with mysqldump cli client" "db\:import:Imports database with mysql cli client according to database defined in env.php" "db\:info:Dumps database informations" "db\:maintain\:check-tables:Check database tables" "db\:query:Executes an SQL query on the database defined in env.php" "db\:status:Shows important server status information or custom selected status values" "db\:variables:Shows important variables or custom selected" "deploy\:mode\:set:Set application mode." "deploy\:mode\:show:Displays current application mode." "design\:demo-notice:Toggles demo store notice for a store view" "dev\:asset\:clear:Clear static assets" "dev\:console:Opens PHP interactive shell with initialized Mage::app\(\) \(Experimental\)" "dev\:module\:create:Create and register a new magento module." "dev\:module\:list:List all installed modules" "dev\:module\:observer\:list:Lists all registered observers" "dev\:report\:count:Get count of report files" "dev\:source-theme\:deploy:Collects and publishes source files for theme." "dev\:symlinks:Toggle allow symlinks setting" "dev\:template-hints:Toggles template hints" "dev\:template-hints-blocks:Toggles template hints block names" "dev\:tests\:run:Runs tests" "dev\:theme\:list:Lists all available themes" "dev\:urn-catalog\:generate:Generates the catalog of URNs to \*.xsd mappings for the IDE to highlight xml." "dev\:xml\:convert:Converts XML file using XSL style sheets" "eav\:attribute\:list:List EAV attributes" "eav\:attribute\:remove:Remove attribute for a given attribute code" "eav\:attribute\:view:View information about an EAV attribute" "generation\:flush:Flushs generated code like factories and proxies" "i18n\:collect-phrases:Discovers phrases in the codebase" "i18n\:pack:Saves language package" "i18n\:uninstall:Uninstalls language packages" "index\:list:Lists all magento indexes" "index\:trigger\:recreate:ReCreate all triggers" "indexer\:info:Shows allowed Indexers" "indexer\:reindex:Reindexes Data" "indexer\:reset:Resets indexer status to invalid" "indexer\:set-mode:Sets index mode type" "indexer\:show-mode:Shows Index Mode" "indexer\:status:Shows status of Indexer" "info\:adminuri:Displays the Magento Admin URI" "info\:backups\:list:Prints list of available backup files" "info\:currency\:list:Displays the list of available currencies" "info\:dependencies\:show-framework:Shows number of dependencies on Magento framework" "info\:dependencies\:show-modules:Shows number of dependencies between modules" "info\:dependencies\:show-modules-circular:Shows number of circular dependencies between modules" "info\:language\:list:Displays the list of available language locales" "info\:timezone\:list:Displays the list of available timezones" "maintenance\:allow-ips:Sets maintenance mode exempt IPs" "maintenance\:disable:Disables maintenance mode" "maintenance\:enable:Enables maintenance mode" "maintenance\:status:Displays maintenance mode status" "media\:dump:Creates an archive with content of media folder." "module\:disable:Disables specified modules" "module\:enable:Enables specified modules" "module\:status:Displays status of modules" "module\:uninstall:Uninstalls modules installed by composer" "sampledata\:deploy:Deploy sample data modules" "sampledata\:remove:Remove all sample data packages from composer.json" "sampledata\:reset:Reset all sample data modules for re-installation" "script\:repo\:list:Lists all scripts in repository" "script\:repo\:run:Run script from repository" "search\:engine\:list:Lists all registered search engines" "setup\:backup:Takes backup of Magento Application code base, media and database" "setup\:config\:set:Creates or modifies the deployment configuration" "setup\:cron\:run:Runs cron job scheduled for setup application" "setup\:db-data\:upgrade:Installs and upgrades data in the DB" "setup\:db-schema\:upgrade:Installs and upgrades the DB schema" "setup\:db\:status:Checks if DB schema or data requires upgrade" "setup\:di\:compile:Generates DI configuration and all missing classes that can be auto-generated" "setup\:install:Installs the Magento application" "setup\:performance\:generate-fixtures:Generates fixtures" "setup\:rollback:Rolls back Magento Application codebase, media and database" "setup\:static-content\:deploy:Deploys static view files" "setup\:store-config\:set:Installs the store configuration" "setup\:uninstall:Uninstalls the Magento application" "setup\:upgrade:Upgrades the Magento application, DB data, and schema" "sys\:check:Checks Magento System" "sys\:cron\:history:Last executed cronjobs with status." "sys\:cron\:list:Lists all cronjobs" "sys\:cron\:run:Runs a cronjob by job code" "sys\:cron\:schedule:Schedule a cronjob for execution right now, by job code" "sys\:info:Prints infos about the current magento system." "sys\:maintenance:Toggles maintenance mode if --on or --off preferences are not set" "sys\:setup\:change-version:Change module resource version" "sys\:setup\:compare-versions:Compare module version with setup_module table." "sys\:setup\:downgrade-versions:Automatically downgrade schema and module versions" "sys\:store\:config\:base-url\:list:Lists all base urls" "sys\:store\:list:Lists all installed store-views" "sys\:url\:list:Get all urls." "sys\:website\:list:Lists all websites" "theme\:uninstall:Uninstalls theme")
    fi

    case $state in
        command)
            _describe 'command' coms
        ;;
        option)
            case "$com" in
                help)
            opts+=("--xml:To output help as XML" "--format:The output format \(txt, xml, json, or md\)" "--raw:To output raw command help")
            ;;
            install)
            opts+=("--magentoVersion:Magento version" "--magentoVersionByName:Magento version name instead of order number" "--installationFolder:Installation folder" "--dbHost:Database host" "--dbUser:Database user" "--dbPass:Database password" "--dbName:Database name" "--dbPort:Database port" "--installSampleData:Install sample data" "--useDefaultConfigParams:Use default installation parameters defined in the yaml file" "--baseUrl:Installation base url" "--replaceHtaccessFile:Generate htaccess file \(for non vhost environment\)" "--noDownload:If set skips download step. Used when installationFolder is already a Magento installation that has to be installed on the given database." "--only-download:Downloads \(and extracts\) source code" "--forceUseDb:If --noDownload passed, force to use given database if it already exists.")
            ;;
            list)
            opts+=("--xml:To output list as XML" "--raw:To output raw command list" "--format:The output format \(txt, xml, json, or md\)")
            ;;
            open-browser)
            opts+=()
            ;;
            script)
            opts+=("--define:Defines a variable" "--stop-on-error:Stops execution of script on error")
            ;;
            shell)
            opts+=()
            ;;
            admin:notifications)
            opts+=("--on:Switch on" "--off:Switch off")
            ;;
            admin:user:change-password)
            opts+=()
            ;;
            admin:user:create)
            opts+=("--admin-user:\(Required\) Admin user" "--admin-password:\(Required\) Admin password" "--admin-email:\(Required\) Admin email" "--admin-firstname:\(Required\) Admin first name" "--admin-lastname:\(Required\) Admin last name" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            admin:user:delete)
            opts+=("--force:Force")
            ;;
            admin:user:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            admin:user:unlock)
            opts+=()
            ;;
            app:config:dump)
            opts+=()
            ;;
            cache:clean)
            opts+=()
            ;;
            cache:disable)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            cache:enable)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            cache:flush)
            opts+=()
            ;;
            cache:list)
            opts+=("--enabled:Filter the list to display only enabled \[1\] or disabled \[0\] cache types" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            cache:report)
            opts+=("--fpc:Use full page cache instead of core cache" "--tags:Output tags" "--mtime:Output last modification time" "--filter-id:Filter output by ID \(substring\)" "--filter-tag:Filter output by TAG \(separate multiple tags by comma\)" "--format:Output Format. One of \[csv, json, xml\]")
            ;;
            cache:status)
            opts+=("--bootstrap:add or override parameters of the bootstrap")
            ;;
            cache:view)
            opts+=("--fpc:Use full page cache instead of core cache" "--unserialize:Unserialize output")
            ;;
            catalog:images:resize)
            opts+=()
            ;;
            catalog:product:attributes:cleanup)
            opts+=()
            ;;
            config:data:acl)
            opts+=()
            ;;
            config:data:di)
            opts+=("--scope:Config scope \(global, adminhtml, frontend, webapi_rest, webapi_soap, ...\)")
            ;;
            config:store:delete)
            opts+=("--scope:The config value\'s scope \(default, websites, stores\)" "--scope-id:The config value\'s scope ID" "--all:Delete all entries by path")
            ;;
            config:store:get)
            opts+=("--scope:The config value\'s scope \(default, websites, stores\)" "--scope-id:The config value\'s scope ID" "--decrypt:Decrypt the config value using env.php\'s crypt key" "--update-script:Output as update script lines" "--magerun-script:Output for usage with config:store:set" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            config:store:set)
            opts+=("--scope:The config value\'s scope \(default, websites, stores\)" "--scope-id:The config value\'s scope ID" "--encrypt:The config value should be encrypted using env.php\'s crypt key" "--no-null:Do not treat value NULL as NULL \(NULL/"unkown" value\) value")
            ;;
            cron:run)
            opts+=("--group:Run jobs only from specified group" "--bootstrap:Add or override parameters of the bootstrap")
            ;;
            customer:create)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            customer:hash:upgrade)
            opts+=()
            ;;
            customer:info)
            opts+=()
            ;;
            customer:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            db:console)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases")
            ;;
            db:create)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases")
            ;;
            db:drop)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--tables:Drop all tables instead of dropping the database" "--force:Force")
            ;;
            db:dump)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--add-time:Append or prepend a timestamp to filename if a filename is provided. Possible values are "suffix", "prefix" or "no"." "--compression:Compress the dump file using one of the supported algorithms" "--only-command:Print only mysqldump command. Do not execute" "--print-only-filename:Execute and prints no output except the dump filename" "--dry-run:Do everything but the actual dump" "--no-single-transaction:Do not use single-transaction \(not recommended, this is blocking\)" "--human-readable:Use a single insert with column names per row. Useful to track database differences. Use db:import --optimize for speeding up the import." "--add-routines:Include stored routines in dump \(procedures \& functions\)" "--stdout:Dump to stdout" "--strip:Tables to strip \(dump only structure of those tables\)" "--exclude:Tables to exclude entirely from the dump \(including structure\)" "--force:Do not prompt if all options are defined")
            ;;
            db:import)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--compression:The compression of the specified file" "--only-command:Print only mysql command. Do not execute" "--only-if-empty:Imports only if database is empty" "--optimize:Convert verbose INSERTs to short ones before import \(not working with compression\)" "--drop:Drop and recreate database before import" "--drop-tables:Drop tables before import")
            ;;
            db:info)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            db:maintain:check-tables)
            opts+=("--type:Check type \(one of QUICK, FAST, MEDIUM, EXTENDED, CHANGED\)" "--repair:Repair tables \(only MyISAM\)" "--table:Process only given table \(wildcards are supported\)" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            db:query)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--only-command:Print only mysql command. Do not execute")
            ;;
            db:status)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--format:Output Format. One of \[csv,json,xml\]" "--rounding:Amount of decimals to display. If -1 then disabled" "--no-description:Disable description")
            ;;
            db:variables)
            opts+=("--connection:Select DB connection type for Magento configurations with several databases" "--format:Output Format. One of \[csv,json,xml\]" "--rounding:Amount of decimals to display. If -1 then disabled" "--no-description:Disable description")
            ;;
            deploy:mode:set)
            opts+=("--skip-compilation:Skips the clearing and regeneration of static content \(generated code, preprocessed CSS, and assets in pub/static/\)")
            ;;
            deploy:mode:show)
            opts+=()
            ;;
            design:demo-notice)
            opts+=("--on:Switch on" "--off:Switch off" "--global:Set value on default scope")
            ;;
            dev:asset:clear)
            opts+=("--theme:Clear assets for specific theme\(s\) only")
            ;;
            dev:console)
            opts+=()
            ;;
            dev:module:create)
            opts+=("--minimal:Create only module file" "--add-blocks:Adds blocks" "--add-helpers:Adds helpers" "--add-models:Adds models" "--add-setup:Adds SQL setup" "--add-all:Adds blocks, helpers and models" "--enable:Enable module after creation" "--modman:Create all files in folder with a modman file." "--add-readme:Adds a readme.md file to generated module" "--add-composer:Adds a composer.json file to generated module" "--author-name:Author for readme.md or composer.json" "--author-email:Author for readme.md or composer.json" "--description:Description for readme.md or composer.json")
            ;;
            dev:module:list)
            opts+=("--vendor:Show modules of a specific vendor \(case insensitive\)" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            dev:module:observer:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]" "--sort:Sort output ascending by event name")
            ;;
            dev:report:count)
            opts+=()
            ;;
            dev:source-theme:deploy)
            opts+=("--type:Type of source files: \[less\]" "--locale:Locale: \[en_US\]" "--area:Area: \[frontend\|adminhtml\]" "--theme:Theme: \[Vendor/theme\]")
            ;;
            dev:symlinks)
            opts+=("--on:Switch on" "--off:Switch off" "--global:Set value on default scope")
            ;;
            dev:template-hints)
            opts+=("--on:Switch on" "--off:Switch off")
            ;;
            dev:template-hints-blocks)
            opts+=("--on:Switch on" "--off:Switch off")
            ;;
            dev:tests:run)
            opts+=()
            ;;
            dev:theme:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            dev:urn-catalog:generate)
            opts+=("--ide:Format in which catalog will be generated. Supported: \[phpstorm\]")
            ;;
            dev:xml:convert)
            opts+=("--overwrite:Overwrite XML file")
            ;;
            eav:attribute:list)
            opts+=("--add-source:Add source models to list" "--add-backend:Add backend type to list" "--filter-type:Filter attributes by entity type" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            eav:attribute:remove)
            opts+=()
            ;;
            eav:attribute:view)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            generation:flush)
            opts+=()
            ;;
            i18n:collect-phrases)
            opts+=("--output:Path \(including filename\) to an output file. With no file specified, defaults to stdout." "--magento:Use the --magento parameter to parse the current Magento codebase. Omit the parameter if a directory is specified.")
            ;;
            i18n:pack)
            opts+=("--mode:Save mode for dictionary\
- "replace" - replace language pack by new one\
- "merge" - merge language packages, by default "replace"" "--allow-duplicates:Use the --allow-duplicates parameter to allow saving duplicates of translate. Otherwise omit the parameter.")
            ;;
            i18n:uninstall)
            opts+=("--backup-code:Take code and configuration files backup \(excluding temporary files\)")
            ;;
            index:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            index:trigger:recreate)
            opts+=()
            ;;
            indexer:info)
            opts+=()
            ;;
            indexer:reindex)
            opts+=()
            ;;
            indexer:reset)
            opts+=()
            ;;
            indexer:set-mode)
            opts+=()
            ;;
            indexer:show-mode)
            opts+=()
            ;;
            indexer:status)
            opts+=()
            ;;
            info:adminuri)
            opts+=()
            ;;
            info:backups:list)
            opts+=()
            ;;
            info:currency:list)
            opts+=()
            ;;
            info:dependencies:show-framework)
            opts+=("--output:Report filename")
            ;;
            info:dependencies:show-modules)
            opts+=("--output:Report filename")
            ;;
            info:dependencies:show-modules-circular)
            opts+=("--output:Report filename")
            ;;
            info:language:list)
            opts+=()
            ;;
            info:timezone:list)
            opts+=()
            ;;
            maintenance:allow-ips)
            opts+=("--none:Clear allowed IP addresses" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            maintenance:disable)
            opts+=("--ip:Allowed IP addresses \(use 'none' to clear allowed IP list\)" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            maintenance:enable)
            opts+=("--ip:Allowed IP addresses \(use 'none' to clear allowed IP list\)" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            maintenance:status)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            media:dump)
            opts+=("--strip:Excludes image cache")
            ;;
            module:disable)
            opts+=("--force:Bypass dependencies check" "--all:Disable all modules" "--clear-static-content:Clear generated static view files. Necessary, if the module\(s\) have static view files" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            module:enable)
            opts+=("--force:Bypass dependencies check" "--all:Enable all modules" "--clear-static-content:Clear generated static view files. Necessary, if the module\(s\) have static view files" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            module:status)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            module:uninstall)
            opts+=("--remove-data:Remove data installed by module\(s\)" "--backup-code:Take code and configuration files backup \(excluding temporary files\)" "--backup-media:Take media backup" "--backup-db:Take complete database backup" "--clear-static-content:Clear generated static view files. Necessary, if the module\(s\) have static view files" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            sampledata:deploy)
            opts+=()
            ;;
            sampledata:remove)
            opts+=()
            ;;
            sampledata:reset)
            opts+=()
            ;;
            script:repo:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            script:repo:run)
            opts+=("--define:Defines a variable" "--stop-on-error:Stops execution of script on error")
            ;;
            search:engine:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            setup:backup)
            opts+=("--code:Take code and configuration files backup \(excluding temporary files\)" "--media:Take media backup" "--db:Take complete database backup" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:config:set)
            opts+=("--backend-frontname:Backend frontname \(will be autogenerated if missing\)" "--key:Encryption key" "--session-save:Session save handler" "--definition-format:Type of definitions used by Object Manager" "--db-host:Database server host" "--db-name:Database name" "--db-user:Database server username" "--db-engine:Database server engine" "--db-password:Database server password" "--db-prefix:Database table prefix" "--db-model:Database type" "--db-init-statements:Database  initial set of commands" "--skip-db-validation:If specified, then db connection validation will be skipped" "--http-cache-hosts:http Cache hosts" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:cron:run)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:db-data:upgrade)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:db-schema:upgrade)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:db:status)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:di:compile)
            opts+=()
            ;;
            setup:install)
            opts+=("--backend-frontname:Backend frontname \(will be autogenerated if missing\)" "--key:Encryption key" "--session-save:Session save handler" "--definition-format:Type of definitions used by Object Manager" "--db-host:Database server host" "--db-name:Database name" "--db-user:Database server username" "--db-engine:Database server engine" "--db-password:Database server password" "--db-prefix:Database table prefix" "--db-model:Database type" "--db-init-statements:Database  initial set of commands" "--skip-db-validation:If specified, then db connection validation will be skipped" "--http-cache-hosts:http Cache hosts" "--base-url:URL the store is supposed to be available at" "--language:Default language code" "--timezone:Default time zone code" "--currency:Default currency code" "--use-rewrites:Use rewrites" "--use-secure:Use secure URLs. Enable this option only if SSL is available." "--base-url-secure:Base URL for SSL connection" "--use-secure-admin:Run admin interface with SSL" "--admin-use-security-key:Whether to use a "security key" feature in Magento Admin URLs and forms" "--admin-user:\(Required\) Admin user" "--admin-password:\(Required\) Admin password" "--admin-email:\(Required\) Admin email" "--admin-firstname:\(Required\) Admin first name" "--admin-lastname:\(Required\) Admin last name" "--cleanup-database:Cleanup the database before installation" "--sales-order-increment-prefix:Sales order number prefix" "--use-sample-data:Use sample data" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:performance:generate-fixtures)
            opts+=("--skip-reindex:Skip reindex")
            ;;
            setup:rollback)
            opts+=("--code-file:Basename of the code backup file in var/backups" "--media-file:Basename of the media backup file in var/backups" "--db-file:Basename of the db backup file in var/backups" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:static-content:deploy)
            opts+=("--dry-run:If specified, then no files will be actually deployed." "--no-javascript:Do not deploy JavaScript files" "--no-css:Do not deploy CSS files." "--no-less:Do not deploy LESS files." "--no-images:Do not deploy images." "--no-fonts:Do not deploy font files." "--no-html:Do not deploy HTML files." "--no-misc:Do not deploy other types of files \(.md, .jbf, .csv, etc...\)." "--no-html-minify:Do not minify HTML files." "--theme:Generate static view files for only the specified themes." "--exclude-theme:Do not generate files for the specified themes." "--language:Generate files only for the specified languages." "--exclude-language:Do not generate files for the specified languages." "--area:Generate files only for the specified areas." "--exclude-area:Do not generate files for the specified areas." "--jobs:Enable parallel processing using the specified number of jobs." "--symlink-locale:Create symlinks for the files of those locales, which are passed for deployment, but have no customizations")
            ;;
            setup:store-config:set)
            opts+=("--base-url:URL the store is supposed to be available at" "--language:Default language code" "--timezone:Default time zone code" "--currency:Default currency code" "--use-rewrites:Use rewrites" "--use-secure:Use secure URLs. Enable this option only if SSL is available." "--base-url-secure:Base URL for SSL connection" "--use-secure-admin:Run admin interface with SSL" "--admin-use-security-key:Whether to use a "security key" feature in Magento Admin URLs and forms" "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:uninstall)
            opts+=("--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            setup:upgrade)
            opts+=("--keep-generated:Prevents generated files from being deleted. \
We discourage using this option except when deploying to production. \
Consult your system integrator or administrator for more information." "--magento-init-params:Add to any command to customize Magento initialization parameters\
For example: "MAGE_MODE=developer\&MAGE_DIRS\[base\]\[path\]=/var/www/example.com\&MAGE_DIRS\[cache\]\[path\]=/var/tmp/cache"")
            ;;
            sys:check)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:cron:history)
            opts+=("--timezone:Timezone to show finished at in" "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:cron:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:cron:run)
            opts+=()
            ;;
            sys:cron:schedule)
            opts+=()
            ;;
            sys:info)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:maintenance)
            opts+=("--on:Set to \[1\] to enable maintenance mode. Optionally supply a comma separated list of IP addresses to exclude from being affected" "--off:Set to \[1\] to disable maintenance mode. Set to \[d\] to also delete the list with excluded IP addresses.")
            ;;
            sys:setup:change-version)
            opts+=()
            ;;
            sys:setup:compare-versions)
            opts+=("--ignore-data:Ignore data updates" "--log-junit:Log output to a JUnit xml file." "--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:setup:downgrade-versions)
            opts+=("--dry-run:Write what to change but do not do any changes")
            ;;
            sys:store:config:base-url:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:store:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            sys:url:list)
            opts+=("--add-categories:Adds categories" "--add-products:Adds products" "--add-cmspages:Adds cms pages" "--add-all:Adds categories, products and cms pages")
            ;;
            sys:website:list)
            opts+=("--format:Output Format. One of \[csv,json,xml\]")
            ;;
            theme:uninstall)
            opts+=("--backup-code:Take code backup \(excluding temporary files\)" "--clear-static-content:Clear generated static view files.")
            ;;

            esac

            _describe 'option' opts
        ;;
        *)
            # fallback to file completion
            _arguments '*:file:_files'
    esac
}

compdef _n98-magerun2 n98-magerun2.phar n98-magerun2 magerun2
