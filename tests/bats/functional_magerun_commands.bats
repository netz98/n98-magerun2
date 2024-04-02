#!/usr/bin/env bats

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'

    declare PHP_BIN
    if ! PHP_BIN=$(which php); then
      echo "Error: PHP binary not found"
      exit 1
    fi

    if [ -z "$N98_MAGERUN2_BIN" ]; then
      echo "ENV variable N98_MAGERUN2_BIN is missing"
      exit 1
    fi

    if [ -z "$N98_MAGERUN2_TEST_MAGENTO_ROOT" ]; then
      echo "ENV variable N98_MAGERUN2_TEST_MAGENTO_ROOT is missing"
      exit 1
    fi

    export BIN="${PHP_BIN} -f ${N98_MAGERUN2_BIN} -- --no-interaction --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
    export BIN_INTERACTION="${PHP_BIN} -f ${N98_MAGERUN2_BIN} -- --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

function cleanup_files_in_magento() {
  rm -Rf "${N98_MAGERUN2_TEST_MAGENTO_ROOT:?}/$1"
}

@test "Issue: 1414" {
  tempfilename=$(mktemp)
  # Generate random numbers for email uniqueness
  random1=$((RANDOM % 1000))
  random2=$((RANDOM % 1000))

  # Use the random numbers to create unique email addresses
  email1="customer${random1}@example.com"
  email2="customer${random2}@example.com"

  # Use the unique email addresses in the script command
  printf "customer:create ${email1} Password1234# Foo Bar 1\ncustomer:create ${email2} Password1234# Foo Bar" > $tempfilename
  run $BIN script $tempfilename
  assert_output --partial "successfully created"
  assert [ "$status" -eq 0 ]
}


@test "Command: admin:user:list" {
	run $BIN "admin:user:list"
	assert_output --partial "username"
}

@test "Command: cache:catalog:image:flush" {
  run $BIN "cache:catalog:image:flush"
  assert_output --partial "Catalog image cache flushed"
  assert [ "$status" -eq 0 ]

  run $BIN "cache:catalog:image:flush" --suppress-event
  assert_output --partial "Catalog image cache flushed"
  assert [ "$status" -eq 0 ]
}

@test "Command: cache:clean" {
	run $BIN "cache:clean" "layout"
	assert_output --partial "cleaned"
}

@test "Command: cache:disable" {
  run $BIN "cache:disable" "full_page"
	assert_output --partial The "following cache types were disabled"
}

@test "Command: cache:enable" {
  run $BIN "cache:enable" "full_page"
  assert_output --partial The "following cache types were enabled"
}

@test "Command: cache:flush" {
  run $BIN "cache:flush"
  assert_output --partial "cache flushed"
}

@test "Command: cache:report" {
  run $BIN "cache:report"
  assert_output --partial "EXPIRE"
}

@test "Command: cache:remove:id" {
  run $BIN "cache:remove:id" "app_action_list"
  assert_output --partial "Cache entry with id app_action_list was removed"

  run $BIN "cache:remove:id" --strict "not_exiting_cache_key_12345"
  assert [ "$status" -eq 1 ]
}

@test "Command: config:data:acl" {
  run $BIN "config:data:acl"
  assert_output --partial "ACL Tree"
}

@test "Command: config:data:di" {
  run $BIN "config:data:di"
  assert_output --partial "DateTimeInterface"
}

@test "Command: config:data:mview" {
  run $BIN "config:data:mview"
  assert_output --partial "catalog_category_flat"

  run $BIN "config:data:mview" -t
  assert_output --partial "MView Data Tree"
}

@test "Command: config:data:indexer" {
  run $BIN "config:data:indexer"
  assert_output --partial "catalog_product_flat"

  run $BIN "config:data:indexer" -t
  assert_output --partial "Indexer Data Tree"
}

@test "Command: config:env:set" {
  run $BIN "config:env:set" magerun.example foo
  assert_output --partial "Config magerun.example successfully set to foo"
}

@test "Command: config:env:delete" {
  run $BIN "config:env:delete" magerun.example
  assert_output --partial "Config magerun.example successfully removed"
}

@test "Command: config:env:show" {
  run $BIN "config:env:show"
  assert_output --partial "backend.frontName"
}

@test "Command: config:search" {
  run $BIN "config:search" "tax"
  assert_output --partial "Sales / Tax"
}

@test "Command: config:store:set" {
  run $BIN "config:store:set" n98/magerun/example defaultValue
  assert_output --partial "n98/magerun/example => defaultValue"

  run $BIN "config:store:set" --scope=stores --scope-id=1 n98/magerun/example myStore2value
  assert_output --partial "n98/magerun/example => myStore2value"
}

@test "Command: config:store:get" {
  run $BIN "config:store:get" n98/magerun/example
  assert_output --partial "n98/magerun/example"
  assert_output --partial "defaultValue"
  assert_output --partial "myStore2value"

  run $BIN "config:store:get" --scope=stores --scope-id=1 n98/magerun/example
  assert_output --partial "myStore2value"

  run $BIN "config:store:get" --scope=stores --scope-id=default n98/magerun/example
  assert_output --partial "myStore2value"

  run $BIN "config:store:get" --scope=stores --scope-id=0 n98/magerun/example
  assert_output --partial "n98/magerun/example"

  run $BIN "config:store:get" --scope=stores --scope-id=admin n98/magerun/example
  assert_output --partial "n98/magerun/example"

  run $BIN "config:store:get" --scope=stores --scope-id=not_existing n98/magerun/example
  assert [ "$status" -eq 1 ]
}

@test "Command: config:store:delete" {
  run $BIN "config:store:delete" n98/magerun/example
  assert_output --partial "deleted path"
}

@test "Command: customer:create" {
  run $BIN "customer:create" "foo@example.com" "Password123" "Firstname" "Lastname"
  assert_output --partial "Customer foo@example.com successfully created"
}

@test "Command: customer:info" {
  run $BIN "customer:info" "foo@example.com"
  assert_output --partial "foo@example.com"
  assert_output --partial "Firstname"
  assert_output --partial "Lastname"
}

@test "Command: customer:list" {
  run $BIN "customer:list"
  assert_output --partial "foo@example.com"
}

@test "Command: customer:change-password" {
  run $BIN "customer:change-password" "foo@example.com" "Password1234"
  assert_output --partial "Password successfully changed"
}

@test "Command: customer:add-address" {
  run $BIN "customer:add-address" foo@example.com base --firstname="John" --lastname="Doe" --street="Pariser Platz" --city="Berlin" --country="DE" --postcode="10117" --telephone="1234567890" --default-billing --default-shipping
  assert_output --partial "Address added successfully to customer foo@example.com"
}

@test "Command: customer:delete" {
  run $BIN "customer:delete" --fuzzy --email=foo --force
  assert_output --partial "Successfully deleted 1 customer/s"
}

@test "Command: db:add-default-authorization-entries" {
  run $BIN "db:add-default-authorization-entries"
  assert_output --partial "OK"
}

@test "Command: db:dump" {
  run $BIN "db:dump" --stdout
  assert [ "$status" -eq 0 ]

  run $BIN "db:dump" "db.sql"
  assert_output --partial "Finished"

  run $BIN "db:dump" --strip=@development db.sql
  assert_output --partial "Finished"

  run $BIN "db:dump" --print-only-filename db.sql
  assert_output --partial "db.sql"

  run $BIN "db:dump" --only-command db.sql
  assert_output --partial "mysqldump"
}

@test "Command: db:info" {
  run $BIN "db:info"
  assert_output --partial "PDO-Connection-String"
}

@test "Command: db:status" {
  run $BIN "db:status"
  assert_output --partial "InnoDB Buffer Pool hit"
}

@test "Command: db:variables" {
  run $BIN "db:variables"
  assert_output --partial "innodb_buffer_pool_size"
}

@test "Command: design:demo-notice" {
  run $BIN "design:demo-notice" --on
  assert_output --partial "Demo Notice enabled for store default"

  run $BIN "design:demo-notice" --off
  assert_output --partial "Demo Notice disabled for store default"
}

#@test "Command: dev:console" {
#  run echo "dev:console --auto-exit 'ls'" | $BIN_INTERACTION
#  assert_output --partial "di"
#}

@test "Command: dev:module:create" {
  run $BIN "dev:module:create" Magerun123 TestModule
  assert_output --partial "Created directory"
  cleanup_files_in_magento "app/code/N98/Magerun123"
}

@test "Command: dev:module:detect-composer-dependencies" {
    if [ -d "${N98_MAGERUN2_TEST_MAGENTO_ROOT}/vendor/magento/module-catalog-rule" ]; then
      run $BIN "dev:module:detect-composer-dependencies" "${N98_MAGERUN2_TEST_MAGENTO_ROOT}/vendor/magento/module-catalog-rule"
      assert_output --partial "magento/module-catalog"
    fi
    if [ -d "${N98_MAGERUN2_TEST_MAGENTO_ROOT}/app/code/Magento/CatalogRule" ]; then
      run $BIN "dev:module:detect-composer-dependencies" "${N98_MAGERUN2_TEST_MAGENTO_ROOT}/app/code/Magento/CatalogRule"
      assert_output --partial "magento/module-catalog"
    fi
}

@test "Command: dev:module:list" {
  run $BIN "dev:module:list"
  assert_output --partial "Magento_Store"
}

@test "Command: dev:module:observer:list" {
  run $BIN "dev:module:observer:list" sales_order_place_after global
  assert_output --partial "Observer name"
}

@test "Command: dev:translate:set" {
  run $BIN "dev:translate:set" foo foo_translate
  assert_output --partial "Translated"

  run $BIN "dev:translate:set" foo foo_translate default
  assert_output --partial "Translated"
}

@test "Command: dev:translate:admin" {
  run $BIN "dev:translate:admin" --on
  assert_output --partial "enabled"

  run $BIN "dev:translate:admin" --off
  assert_output --partial "disabled"
}

@test "Command: dev:translate:export" {
  run $BIN "dev:translate:export" en_US
  assert_output --partial "Exporting"
}

@test "Command: dev:translate:shop" {
  run $BIN "dev:translate:shop" --on
  assert_output --partial "enabled"

  run $BIN "dev:translate:shop" --off
  assert_output --partial "disabled"
}

@test "Command: dev:symlinks" {
  run $BIN "dev:symlinks" --on
  assert_output --partial "allowed"

  run $BIN "dev:symlinks" --off
  assert_output --partial "denied"
}

@test "Command: dev:template-hints" {
  run $BIN "dev:template-hints" --on
  assert_output --partial "enabled"

  run $BIN "dev:template-hints" --off
  assert_output --partial "disabled"
}

@test "Command: dev-template-hints-blocks" {
  run $BIN "dev:template-hints-blocks" --on
  assert_output --partial "enabled"

  run $BIN "dev:template-hints-blocks" --off
  assert_output --partial "disabled"
}

@test "Command: dev:theme:list" {
  run $BIN "dev:theme:list"
  assert_output --partial "Magento/backend"
}

@test "Command: dev:encrypt & dev:decrypt" {
  result=$($BIN "dev:encrypt" "testValue")
  run $BIN "dev:decrypt" "${result}"
  assert_output "testValue"
}

@test "Command: eav:attribute:list" {
  run $BIN "eav:attribute:list"
  assert_output --partial "sku"
}

@test "Command: eav:attribute:view" {
  run $BIN "eav:attribute:view" catalog_product sku
  assert_output --partial "catalog_product_entity"
}

@test "Command: github:pr" {
  run $BIN "github:pr" 21787
  assert_output --partial "x_forwarded_for"

  run $BIN "github:pr" --patch 21787
  assert_output --partial "PR-21787-magento-magento2.patch"

  run $BIN "github:pr" --diff 21787
  assert_output --partial "setXForwardedFor"

  run $BIN "github:pr" --mage-os 1
  assert_output --partial "automatically"

  run $BIN "github:pr" --mage-os --patch 1
  assert_output --partial "PR-1-mage-os-mageos-magento2.patch"

  run $BIN "github:pr" --mage-os --diff 1
  assert_output --partial "server_url"
}

@test "Command: generation:flush" {
  run $BIN "generation:flush" Symfony
  assert_output --partial "Removed"
  assert_output --partial "Symfony"
}

@test "Command: index:list" {
  run $BIN "index:list"
  assert_output --partial "catalogsearch_fulltext"
}

@test "Command: index:trigger:recreate" {
  run $BIN indexer:set-mode realtime catalog_product_price
  run $BIN "index:trigger:recreate"
  assert_output --partial 'Skipped indexer Product Price. Mode must be "schedule".'

  run $BIN indexer:set-mode schedule catalog_product_price
  run $BIN "index:trigger:recreate"
  assert_output --partial "Re-created triggers of indexer Product Price"
}

@test "Command: Integration:create" {
  run $BIN "integration:create" magerun-test magerun@example.com https://localhost
  assert_output --partial "Integration ID"
}

@test "Command: integration:list" {
  run $BIN "integration:list"
  assert_output --partial "magerun-test"
}

@test "Command: integration:show" {
  run $BIN "integration:show" magerun-test
  assert_output --partial "magerun-test"
  assert_output --partial "Consumer Key"

  run $BIN "integration:show" magerun-test name
  assert_output --partial "magerun-test"

  run $BIN "integration:show" magerun-test --format=json
  assert_output --partial "Consumer Key"

  run $BIN "integration:show" magerun-test --format=yaml
  assert_output --partial "Consumer Key"
}

@test "Command: integration:delete" {
  run $BIN "integration:delete" magerun-test
  assert_output --partial "Successfully deleted integration"
}

@test "Command: media:dump" {
  run $BIN "media:dump"
  assert_output --partial "Compress"
}

@test "Command: script:repo:list" {
  run $BIN "script:repo:list"
  assert_output --partial "Script"
}

@test "Command: search:engine:list" {
  run $BIN "search:engine:list"
  assert_output --partial "label"
}

@test "Command: sys:check" {
  run $BIN "sys:check"
  assert_output --partial "Env"
}

@test "Command: sys:cron:kill" {
  run $BIN "sys:cron:kill" "not_exiting_job_code"
  assert_output --partial "No process found to kill"
}

@test "Command: sys:cron:list" {
  run $BIN "sys:cron:list"
  assert_output --partial "indexer_reindex_all_invalid"
}

@test "Command: sys:cron:run" {
  run $BIN "sys:cron:run" "sales_clean_quotes"
  assert_output --partial "done"
}

@test "Command: sys:cron:schedule" {
  run $BIN "sys:cron:schedule" sales_clean_quotes
  assert_output --partial "done"
}

@test "Command: sys:cron:history" {
  run $BIN "sys:cron:history"
  assert_output --partial "Last executed jobs"
}

@test "Command: sys:info" {
  run $BIN "sys:info"
  assert_output --partial "Magento System Information"
}

@test "Command: sys:maintenance" {
  run $BIN "sys:maintenance" --on
  assert_output --partial "on"

  run $BIN "sys:maintenance" --off
  assert_output --partial "off"
}

@test "Command: sys:setup:compare-versions" {
  run $BIN "sys:setup:compare-versions"
  assert_output --partial "Setup"
}

@test "Command: sys:setup:downgrade-versions" {
  run $BIN "sys:setup:downgrade-versions"
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:store:config:base-url:list" {
  run $BIN "sys:store:config:base-url:list"
  assert_output --partial "unsecure_baseurl"
}

@test "Command: sys:store:list" {
  run $BIN "sys:store:list"
  assert_output --partial "default"
}

@test "Command: sys:url:list" {
  run $BIN "sys:url:list" --add-cmspages default '{host},{path}'
  assert_output --partial "/"
}

@test "Command: sys:website:list" {
  run $BIN "sys:website:list"
  assert_output --partial "base"

  run $BIN "sys:website:list" --format=csv
  assert_output --partial "1,base"
}
