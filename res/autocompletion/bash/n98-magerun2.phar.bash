#!/bin/bash
# Installation:
#  Copy to /etc/bash_completion.d/n98-magerun.phar
# or
#  Append to ~/.bash_completion
# open new or restart existing shell session


_n98-magerun2()
{
    local cur script coms opts com
    COMPREPLY=()
    _get_comp_words_by_ref -n : cur words

    # for an alias, get the real script behind it
    if [[ $(type -t ${words[0]}) == "alias" ]]; then
        script=$(alias ${words[0]} | sed -E "s/alias ${words[0]}='(.*)'/\1/")
    else
        script=${words[0]}
    fi

    # lookup for command
    for word in ${words[@]:1}; do
        if [[ $word != -* ]]; then
            com=$word
            break
        fi
    done

    # completing for an option
    if [[ ${cur} == --* ]] ; then
        opts="--help --quiet --verbose --version --ansi --no-ansi --no-interaction --root-dir --skip-config --skip-root-check --skip-core-commands"

        case "$com" in
            help)
            opts="${opts} --xml --format --raw"
            ;;
            install)
            opts="${opts} --magentoVersion --magentoVersionByName --installationFolder --dbHost --dbUser --dbPass --dbName --dbPort --installSampleData --useDefaultConfigParams --baseUrl --replaceHtaccessFile --noDownload --only-download --forceUseDb"
            ;;
            list)
            opts="${opts} --xml --raw --format"
            ;;
            open-browser)
            opts="${opts} "
            ;;
            script)
            opts="${opts} --define --stop-on-error"
            ;;
            shell)
            opts="${opts} "
            ;;
            admin:notifications)
            opts="${opts} --on --off"
            ;;
            admin:user:change-password)
            opts="${opts} "
            ;;
            admin:user:create)
            opts="${opts} --admin-user --admin-password --admin-email --admin-firstname --admin-lastname --magento-init-params"
            ;;
            admin:user:delete)
            opts="${opts} --force"
            ;;
            admin:user:list)
            opts="${opts} --format"
            ;;
            admin:user:unlock)
            opts="${opts} "
            ;;
            cache:clean)
            opts="${opts} "
            ;;
            cache:disable)
            opts="${opts} --format"
            ;;
            cache:enable)
            opts="${opts} --format"
            ;;
            cache:flush)
            opts="${opts} "
            ;;
            cache:list)
            opts="${opts} --enabled --format"
            ;;
            cache:status)
            opts="${opts} --bootstrap"
            ;;
            catalog:images:resize)
            opts="${opts} "
            ;;
            catalog:product:attributes:cleanup)
            opts="${opts} "
            ;;
            config:store:delete)
            opts="${opts} --scope --scope-id --all"
            ;;
            config:store:get)
            opts="${opts} --scope --scope-id --decrypt --update-script --magerun-script --format"
            ;;
            config:store:set)
            opts="${opts} --scope --scope-id --encrypt --no-null"
            ;;
            cron:run)
            opts="${opts} --group --bootstrap"
            ;;
            customer:create)
            opts="${opts} --format"
            ;;
            customer:hash:upgrade)
            opts="${opts} "
            ;;
            customer:info)
            opts="${opts} "
            ;;
            customer:list)
            opts="${opts} --format"
            ;;
            db:console)
            opts="${opts} --connection"
            ;;
            db:create)
            opts="${opts} --connection"
            ;;
            db:drop)
            opts="${opts} --connection --tables --force"
            ;;
            db:dump)
            opts="${opts} --connection --add-time --compression --only-command --print-only-filename --dry-run --no-single-transaction --human-readable --add-routines --stdout --strip --force"
            ;;
            db:import)
            opts="${opts} --connection --compression --only-command --only-if-empty --optimize --drop --drop-tables"
            ;;
            db:info)
            opts="${opts} --connection --format"
            ;;
            db:maintain:check-tables)
            opts="${opts} --type --repair --table --format"
            ;;
            db:query)
            opts="${opts} --connection --only-command"
            ;;
            db:status)
            opts="${opts} --connection --format --rounding --no-description"
            ;;
            db:variables)
            opts="${opts} --connection --format --rounding --no-description"
            ;;
            deploy:mode:set)
            opts="${opts} --skip-compilation"
            ;;
            deploy:mode:show)
            opts="${opts} "
            ;;
            design:demo-notice)
            opts="${opts} --on --off --global"
            ;;
            dev:console)
            opts="${opts} "
            ;;
            dev:module:create)
            opts="${opts} --minimal --add-blocks --add-helpers --add-models --add-setup --add-all --enable --modman --add-readme --add-composer --author-name --author-email --description"
            ;;
            dev:module:list)
            opts="${opts} --vendor --format"
            ;;
            dev:module:observer:list)
            opts="${opts} --format --sort"
            ;;
            dev:report:count)
            opts="${opts} "
            ;;
            dev:source-theme:deploy)
            opts="${opts} --type --locale --area --theme"
            ;;
            dev:symlinks)
            opts="${opts} --on --off --global"
            ;;
            dev:template-hints)
            opts="${opts} --on --off"
            ;;
            dev:template-hints-blocks)
            opts="${opts} --on --off"
            ;;
            dev:tests:run)
            opts="${opts} "
            ;;
            dev:theme:list)
            opts="${opts} --format"
            ;;
            dev:urn-catalog:generate)
            opts="${opts} --ide"
            ;;
            dev:xml:convert)
            opts="${opts} --overwrite"
            ;;
            eav:attribute:view)
            opts="${opts} --format"
            ;;
            generation:flush)
            opts="${opts} "
            ;;
            i18n:collect-phrases)
            opts="${opts} --output --magento"
            ;;
            i18n:pack)
            opts="${opts} --mode --allow-duplicates"
            ;;
            i18n:uninstall)
            opts="${opts} --backup-code"
            ;;
            index:list)
            opts="${opts} --format"
            ;;
            indexer:info)
            opts="${opts} "
            ;;
            indexer:reindex)
            opts="${opts} "
            ;;
            indexer:reset)
            opts="${opts} "
            ;;
            indexer:set-mode)
            opts="${opts} "
            ;;
            indexer:show-mode)
            opts="${opts} "
            ;;
            indexer:status)
            opts="${opts} "
            ;;
            info:adminuri)
            opts="${opts} "
            ;;
            info:backups:list)
            opts="${opts} "
            ;;
            info:currency:list)
            opts="${opts} "
            ;;
            info:dependencies:show-framework)
            opts="${opts} --output"
            ;;
            info:dependencies:show-modules)
            opts="${opts} --output"
            ;;
            info:dependencies:show-modules-circular)
            opts="${opts} --output"
            ;;
            info:language:list)
            opts="${opts} "
            ;;
            info:timezone:list)
            opts="${opts} "
            ;;
            maintenance:allow-ips)
            opts="${opts} --none --magento-init-params"
            ;;
            maintenance:disable)
            opts="${opts} --ip --magento-init-params"
            ;;
            maintenance:enable)
            opts="${opts} --ip --magento-init-params"
            ;;
            maintenance:status)
            opts="${opts} --magento-init-params"
            ;;
            module:disable)
            opts="${opts} --force --all --clear-static-content --magento-init-params"
            ;;
            module:enable)
            opts="${opts} --force --all --clear-static-content --magento-init-params"
            ;;
            module:status)
            opts="${opts} --magento-init-params"
            ;;
            module:uninstall)
            opts="${opts} --remove-data --backup-code --backup-media --backup-db --clear-static-content --magento-init-params"
            ;;
            sampledata:deploy)
            opts="${opts} "
            ;;
            sampledata:remove)
            opts="${opts} "
            ;;
            sampledata:reset)
            opts="${opts} "
            ;;
            script:repo:list)
            opts="${opts} --format"
            ;;
            script:repo:run)
            opts="${opts} --define --stop-on-error"
            ;;
            setup:backup)
            opts="${opts} --code --media --db --magento-init-params"
            ;;
            setup:config:set)
            opts="${opts} --backend-frontname --key --session-save --definition-format --db-host --db-name --db-user --db-engine --db-password --db-prefix --db-model --db-init-statements --skip-db-validation --http-cache-hosts --magento-init-params"
            ;;
            setup:cron:run)
            opts="${opts} --magento-init-params"
            ;;
            setup:db-data:upgrade)
            opts="${opts} --magento-init-params"
            ;;
            setup:db-schema:upgrade)
            opts="${opts} --magento-init-params"
            ;;
            setup:db:status)
            opts="${opts} --magento-init-params"
            ;;
            setup:di:compile)
            opts="${opts} "
            ;;
            setup:install)
            opts="${opts} --backend-frontname --key --session-save --definition-format --db-host --db-name --db-user --db-engine --db-password --db-prefix --db-model --db-init-statements --skip-db-validation --http-cache-hosts --base-url --language --timezone --currency --use-rewrites --use-secure --base-url-secure --use-secure-admin --admin-use-security-key --admin-user --admin-password --admin-email --admin-firstname --admin-lastname --cleanup-database --sales-order-increment-prefix --use-sample-data --magento-init-params"
            ;;
            setup:performance:generate-fixtures)
            opts="${opts} --skip-reindex"
            ;;
            setup:rollback)
            opts="${opts} --code-file --media-file --db-file --magento-init-params"
            ;;
            setup:static-content:deploy)
            opts="${opts} --dry-run --no-javascript --no-css --no-less --no-images --no-fonts --no-html --no-misc --no-html-minify --theme --exclude-theme --language --exclude-language --area --exclude-area --jobs"
            ;;
            setup:store-config:set)
            opts="${opts} --base-url --language --timezone --currency --use-rewrites --use-secure --base-url-secure --use-secure-admin --admin-use-security-key --magento-init-params"
            ;;
            setup:uninstall)
            opts="${opts} --magento-init-params"
            ;;
            setup:upgrade)
            opts="${opts} --keep-generated --magento-init-params"
            ;;
            sys:check)
            opts="${opts} --format"
            ;;
            sys:cron:history)
            opts="${opts} --timezone --format"
            ;;
            sys:cron:list)
            opts="${opts} --format"
            ;;
            sys:cron:run)
            opts="${opts} "
            ;;
            sys:cron:schedule)
            opts="${opts} "
            ;;
            sys:info)
            opts="${opts} --format"
            ;;
            sys:maintenance)
            opts="${opts} --on --off"
            ;;
            sys:setup:change-version)
            opts="${opts} "
            ;;
            sys:setup:compare-versions)
            opts="${opts} --ignore-data --log-junit --format"
            ;;
            sys:setup:downgrade-versions)
            opts="${opts} --dry-run"
            ;;
            sys:store:config:base-url:list)
            opts="${opts} --format"
            ;;
            sys:store:list)
            opts="${opts} --format"
            ;;
            sys:url:list)
            opts="${opts} --add-categories --add-products --add-cmspages --add-all"
            ;;
            sys:website:list)
            opts="${opts} --format"
            ;;
            theme:uninstall)
            opts="${opts} --backup-code --clear-static-content"
            ;;

        esac

        COMPREPLY=($(compgen -W "${opts}" -- ${cur}))
        __ltrim_colon_completions "$cur"

        return 0;
    fi

    # completing for a command
    if [[ $cur == $com ]]; then
        coms="help install list open-browser script shell admin:notifications admin:user:change-password admin:user:create admin:user:delete admin:user:list admin:user:unlock cache:clean cache:disable cache:enable cache:flush cache:list cache:status catalog:images:resize catalog:product:attributes:cleanup config:store:delete config:store:get config:store:set cron:run customer:create customer:hash:upgrade customer:info customer:list db:console db:create db:drop db:dump db:import db:info db:maintain:check-tables db:query db:status db:variables deploy:mode:set deploy:mode:show design:demo-notice dev:console dev:module:create dev:module:list dev:module:observer:list dev:report:count dev:source-theme:deploy dev:symlinks dev:template-hints dev:template-hints-blocks dev:tests:run dev:theme:list dev:urn-catalog:generate dev:xml:convert eav:attribute:view generation:flush i18n:collect-phrases i18n:pack i18n:uninstall index:list indexer:info indexer:reindex indexer:reset indexer:set-mode indexer:show-mode indexer:status info:adminuri info:backups:list info:currency:list info:dependencies:show-framework info:dependencies:show-modules info:dependencies:show-modules-circular info:language:list info:timezone:list maintenance:allow-ips maintenance:disable maintenance:enable maintenance:status module:disable module:enable module:status module:uninstall sampledata:deploy sampledata:remove sampledata:reset script:repo:list script:repo:run setup:backup setup:config:set setup:cron:run setup:db-data:upgrade setup:db-schema:upgrade setup:db:status setup:di:compile setup:install setup:performance:generate-fixtures setup:rollback setup:static-content:deploy setup:store-config:set setup:uninstall setup:upgrade sys:check sys:cron:history sys:cron:list sys:cron:run sys:cron:schedule sys:info sys:maintenance sys:setup:change-version sys:setup:compare-versions sys:setup:downgrade-versions sys:store:config:base-url:list sys:store:list sys:url:list sys:website:list theme:uninstall"

        COMPREPLY=($(compgen -W "${coms}" -- ${cur}))
        __ltrim_colon_completions "$cur"

        return 0
    fi
}

complete -o default -F _n98-magerun2 n98-magerun2.phar n98-magerun2 magerun2
