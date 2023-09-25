#!/usr/bin/env bats

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'

    declare PHP_BIN
    PHP_BIN=$(which php)
    if [ $? -ne 0 ]; then
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
}

@test "Command: admin:user:create" {
    run $BIN "admin:user:create" --admin-user=foo --admin-password=Password123 --admin-email=foo@example.com --admin-firstname=Foo --admin-lastname=Foo
    assert_output --partial "Created Magento administrator user named foo"
}

@test "Command: admin:user:unlock" {
  run $BIN "admin:user:unlock" xyz
  assert_output --partial "Couldn't find the user account"
}

@test "Command: app:config:dump" {
  run $BIN "app:config:dump"
  assert_output --partial "Done"
}

@test "Command: app:config:import" {
  run $BIN "app:config:import"
  assert_output --partial "Nothing to import."
}

@test "Command: app:config:status" {
  run $BIN "app:config:status"
  assert_output --partial "Config files are up to date."
}

@test "Command: cache:clean" {
  run $BIN "cache:clean"
  assert_output --partial "cache cleaned"
}

@test "Command: cache:disable" {
  run $BIN "cache:disable" full_page
  assert_output --partial "The following cache types were disabled"
}

@test "Command: cache:enable" {
  run $BIN "cache:enable" full_page
  assert_output --partial "The following cache types were enabled"
}

@test "Command: cache:flush" {
  run $BIN "cache:flush"
  assert_output --partial "config cache flushed"
}

@test "Command: cache:status" {
  run $BIN "cache:status"
  assert_output --partial "Current status"
}

# @test "Command: catalog:product:attributes:cleanup" {
#   run $BIN "catalog:product:attributes:cleanup"
#   assert_output --partial "Unused product attributes successfully cleaned up"
# }

@test "Command: config:show" {
  run $BIN "config:show"
  assert_output --partial "catalog/category/root_id"
}

@test "Command: customer:hash:upgrade" {
  run $BIN "customer:hash:upgrade"
  assert_output --partial "Finished"
}

@test "Command: dev:di:info" {
  run $BIN "dev:di:info" Magento\\Catalog\\Api\\Data\\ProductInterface
  assert_output --partial "DI configuration for the class"
}

@test "Command: dev:profiler:disable" {
  run $BIN "dev:profiler:disable"
  assert_output --partial "Profiler disabled."
}

@test "Command: dev:profiler:enable" {
  run $BIN "dev:profiler:enable"
  assert_output --partial "Profiler enabled with html output."
}

@test "Command: dev:query-log:disable" {
  run $BIN "dev:query-log:disable"
  assert_output --partial "DB query logging disabled."
}

@test "Command: dev:query-log:enable" {
  run $BIN "dev:query-log:enable"
  assert_output --partial "DB query logging enabled."
}

@test "Command: dev:template-hints:disable" {
  run $BIN "dev:template-hints:disable"
  assert_output --partial "Template hints disabled. Refresh cache types"
}

@test "Command: dev:template-hints:enable" {
  run $BIN "dev:template-hints:enable"
  assert_output --partial "Template hints enabled."
}

@test "Command: downloadable:domains:add" {
  run $BIN "downloadable:domains:add" example.com
  assert_output --partial "example.com was added to the whitelist."
}

@test "Command: downloadable:domains:show" {
  run $BIN "downloadable:domains:show"
  assert_output --partial "example.com"
}

@test "Command: downloadable:domains:remove" {
  run $BIN "downloadable:domains:remove" example.com
  assert_output --partial "example.com was removed from the whitelist."
}

@test "Command: indexer:info" {
  run $BIN "indexer:info"
  assert_output --partial "catalog_category_product"
}

@test "Command: indexer:reindex" {
  run $BIN "indexer:reindex"
  assert_output --partial "Catalog Search index has been rebuilt successfully"
}

@test "Command: indexer:reset" {
  run $BIN "indexer:reset"
  assert_output --partial "Catalog Search indexer has been invalidated."
}

@test "Command: indexer:set-dimensions-mode" {
  run $BIN "indexer:set-dimensions-mode"
  assert_output --partial "Indexer"
}

@test "Command: indexer:set-mode" {
  run $BIN "indexer:set-mode" realtime
  assert_output --partial "Index mode for Indexer"
}

@test "Command: indexer:show-dimensions-mode" {
  run $BIN "indexer:show-dimensions-mode"
  assert_output --partial "Product Price"
}

@test "Command: indexer:show-mode" {
  run $BIN "indexer:show-mode"
  assert_output --partial "Catalog Search"
}

@test "Command: indexer:status" {
  run $BIN "indexer:status"
  assert_output --partial "Update On"
}

@test "Command: info:adminuri" {
  run $BIN "info:adminuri"
  assert_output --partial "Admin URI:"
}

@test "Command: info:backup:list" {
  run $BIN "info:backup:list"
  assert_output --partial "No backup files found."
}

@test "Command: info:currency:list" {
  run $BIN "info:currency:list"
  assert_output --partial "S Dollar (USD)"
}

@test "Command: info:dependencies:show-modules" {
  run $BIN "info:dependencies:show-modules"
  assert_output --partial "Report successfully processed."
}

@test "Command: info:language:list" {
  run $BIN "info:language:list"
  assert_output --partial "German (Germany)"
}

@test "Command: info:timezone:list" {
  run $BIN "info:timezone:list"
  assert_output --partial "Europe/Berlin"
}

@test "Command: maintenance:allow-ips" {
  run $BIN "maintenance:allow-ips" 127.0.0.1
  assert_output --partial "Set exempt IP-addresses: 127.0.0.1"
}

@test "Command: maintenance:enable" {
  run $BIN "maintenance:enable"
  assert_output --partial "Enabled maintenance mode"
}

@test "Command: maintenance:disable" {
  run $BIN "maintenance:disable"
  assert_output --partial "Disabled maintenance mode"
}

@test "Command: maintenance:status" {
  run $BIN "maintenance:status"
  assert_output --partial "Status: maintenance mode is not active"
}

@test "Command: setup:upgrade" {
  run $BIN "setup:upgrade"
  assert_output --partial "Updating modules"
}

@test "Command: module:config:status" {
  run $BIN "module:config:status"
  assert_output --partial "The modules configuration is up to date."
}

@test "Command: module:status" {
  run $BIN "module:status"
  assert_output --partial "List of enabled modules"
}

@test "Command: queue:consumers:list" {
  run $BIN "queue:consumers:list"
  assert_output --partial "async.operations.all"
}

@test "Command: setup:db-data:upgrade" {
  run $BIN "setup:db-data:upgrade"
  assert_output --partial "Data install/update"
}

@test "Command: setup:db-schema:upgrade" {
  run $BIN "setup:db-schema:upgrade"
  assert_output --partial "Schema creation/updates"
}

# @test "Command: setup:db:status" {
#   run $BIN "setup:db:status"
#   assert_output --partial "All modules are up to date."
# }

@test "Command: store:list" {
  run $BIN "store:list"
  assert_output --partial "Website ID"
}

@test "Command: store:website:list" {
  run $BIN "store:website:list"
  assert_output --partial "Admin"
}

@test "Command: varnish:vcl:generate" {
  run $BIN "varnish:vcl:generate"
  assert_output --partial "vcl 4.0"
}

@test "Command: route:list -m Magento_Backend -a adminhtml" {
  run $BIN "route:list" -m Magento_Backend -a adminhtml
  assert_output --partial "admin/dashboard/index"
}

@test "Command: route:list -m Magento_Multishipping -a frontend" {
  run $BIN "route:list" -m Magento_Multishipping -a frontend
  assert_output --partial "multishipping/checkout_address/editaddress"
}
