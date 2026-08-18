#!/usr/bin/env bats

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'
    load 'test_helper/distribution'

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
    export MAGERUN_SRC_ROOT="$(dirname "$(dirname "$(realpath "${BATS_TEST_DIRNAME}")")")"
    export BIN_INTERACTION="${PHP_BIN} -f ${N98_MAGERUN2_BIN} -- --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

function cleanup_files_in_magento() {
  rm -Rf "${N98_MAGERUN2_TEST_MAGENTO_ROOT:?}/$1"
}

function extract_store_id() {
  sed -n 's/.*ID: \([0-9][0-9]*\).*/\1/p' | sed -n '$p'
}

function create_test_admin_user() {
  local username="$1"
  local is_active="$2"

  php "${N98_MAGERUN2_TEST_MAGENTO_ROOT}/bin/magento" admin:user:create \
    --admin-user="${username}" \
    --admin-password="BatsPassw0rd#1" \
    --admin-email="${username}@example.com" \
    --admin-firstname="Bats" \
    --admin-lastname="User" >/dev/null 2>&1

  if [ "${is_active}" = "0" ]; then
    $BIN "db:query" "UPDATE admin_user SET is_active = 0 WHERE username = '${username}'" >/dev/null 2>&1
  fi
}

teardown() {
  if [ -n "${store_id:-}" ]; then
    $BIN "sys:store:delete" "$store_id" --force >/dev/null 2>&1 || true
  fi
  if [ -n "${admin_test_username:-}" ]; then
    $BIN "db:query" "DELETE FROM admin_user WHERE username = '${admin_test_username}'" >/dev/null 2>&1 || true
  fi
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

# ============================================
# Test Application
# ============================================

@test "Test Application - list" {
  run $BIN "list"
  assert_output --partial "n98-magerun2"
}

@test "Test Application - list with ANSI output" {
  run $BIN --ansi "list"
  assert_success
  assert_output --partial "COMMANDS"
  refute_output --partial "Incorrectly nested style tag found"
}

@test "Test Application - help" {
  run $BIN "list"
  assert_output --partial "Display help for a command"
}

@test "Test Application - help sys:info" {
  run $BIN "help" "sys:info"
  assert_output --partial "sys:info [options] [--] [<key>]"
}

@test "Test Application --add-module-dir" {
  # Call the test command in the example module in the tests/_files/modules/example-module directory
  run $BIN -vvv --add-module-dir=${MAGERUN_SRC_ROOT}/tests/_files/modules "magerun:example-module:test"
  assert_output --partial "Successfully executed example module command!"
  assert [ "$status" -eq 0 ]
}

@test "Test Application --skip-config" {
  run $BIN -vvv --add-module-dir=${MAGERUN_SRC_ROOT}/tests/_files/modules --skip-config

  # no module should be loaded -> --skip-config means no module config should be loaded
  refute_output "Load additional module config"
}

# ============================================
# Command: admin:user:list
# ============================================

@test "Command: admin:user:list (default columns)" {
  run $BIN "admin:user:list"
  assert_output --partial "id"
  assert_output --partial "username"
  assert_output --partial "email"
  assert_output --partial "status"
  assert_output --partial "logdate"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (header columns)" {
  run $BIN "admin:user:list"
  headers=$(echo "$output" | grep '^|' | head -n 1)
  echo "$headers" | grep -q '| id '
  echo "$headers" | grep -q '| username '
  echo "$headers" | grep -q '| email '
  echo "$headers" | grep -q '| status '
  echo "$headers" | grep -q '| logdate '
}

@test "Command: admin:user:list (sort by username)" {
  run $BIN "admin:user:list" --sort=username
  assert_output --partial "id"
  assert_output --partial "username"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (sort by id)" {
  run $BIN "admin:user:list" --sort=id
  assert_output --partial "id"
  assert_output --partial "username"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (sort by user_id)" {
  run $BIN "admin:user:list" --sort=user_id
  assert_output --partial "id"
  assert_output --partial "username"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (additional columns)" {
  run $BIN "admin:user:list" --columns="user_id,firstname,lastname,email,logdate"
  assert_output --partial "firstname"
  assert_output --partial "lastname"
  assert_output --partial "email"
  assert_output --partial "logdate"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (user_id only)" {
  run $BIN "admin:user:list" --columns="user_id"
  assert_output --partial "id"
  refute_output --partial "username"
}

@test "Command: admin:user:list (all columns)" {
  run $BIN "admin:user:list" --columns="user_id,firstname,lastname,email,username,password,created,modified,logdate,lognum,reload_acl_flag,is_active,extra,rp_token,rp_token_created_at,interface_locale,failures_num,first_failure,lock_expires"
  assert_output --partial "id"
  assert_output --partial "firstname"
  assert_output --partial "lastname"
  assert_output --partial "created"
  assert_output --partial "status"
  assert_output --partial "lock_expires"
  assert [ "$status" -eq 0 ]
}

@test "Command: admin:user:list (sort by email desc)" {
  run $BIN "admin:user:list" --sort=email --sort-order=desc
  assert_output --partial "email"
  assert_output --partial "id"
  assert_output --partial "status"
  assert [ "$status" -eq 0 ]
}

# ============================================
# Command: admin:user:enable / admin:user:activate
# ============================================

@test "Command: admin:user:enable activates the given user" {
  admin_test_username="bats_enable_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 0

  run $BIN "admin:user:enable" "$admin_test_username"
  assert [ "$status" -eq 0 ]
  assert_output --partial "User has been"
  assert_output --partial "activated"

  run $BIN "admin:user:list" --format=csv --columns=username,is_active
  assert_output --partial "${admin_test_username},active"
}

@test "Command: admin:user:activate is an alias for admin:user:enable" {
  admin_test_username="bats_activate_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 0

  run $BIN "admin:user:activate" "$admin_test_username"
  assert [ "$status" -eq 0 ]
  assert_output --partial "activated"
}

@test "Command: admin:user:enable fails without a username when not interactive" {
  run $BIN "admin:user:enable"
  assert [ "$status" -eq 1 ]
  assert_output --partial "Please provide an username or email"
}

@test "Command: admin:user:enable prompts for a user to select when omitted" {
  admin_test_username="bats_enable_prompt_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 0

  run bash -c "printf '%s\\n' \"$admin_test_username\" | $BIN_INTERACTION admin:user:enable"
  assert [ "$status" -eq 0 ]
  assert_output --partial "User has been"
  assert_output --partial "activated"
}

# ============================================
# Command: admin:user:disable / admin:user:deactivate
# ============================================

@test "Command: admin:user:disable deactivates the given user" {
  admin_test_username="bats_disable_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 1

  run $BIN "admin:user:disable" "$admin_test_username"
  assert [ "$status" -eq 0 ]
  assert_output --partial "User has been"
  assert_output --partial "deactivated"

  run $BIN "admin:user:list" --format=csv --columns=username,is_active
  assert_output --partial "${admin_test_username},inactive"
}

@test "Command: admin:user:deactivate is an alias for admin:user:disable" {
  admin_test_username="bats_deactivate_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 1

  run $BIN "admin:user:deactivate" "$admin_test_username"
  assert [ "$status" -eq 0 ]
  assert_output --partial "deactivated"
}

@test "Command: admin:user:disable fails without a username when not interactive" {
  run $BIN "admin:user:disable"
  assert [ "$status" -eq 1 ]
  assert_output --partial "Please provide an username or email"
}

@test "Command: admin:user:disable prompts for a user to select when omitted" {
  admin_test_username="bats_disable_prompt_$(date +%s%N)"
  create_test_admin_user "$admin_test_username" 1

  run bash -c "printf '%s\\n' \"$admin_test_username\" | $BIN_INTERACTION admin:user:disable"
  assert [ "$status" -eq 0 ]
  assert_output --partial "User has been"
  assert_output --partial "deactivated"
}

# ============================================
# Command: cache:catalog:image:flush
# ============================================

@test "Command: cache:catalog:image:flush" {
  run $BIN "cache:catalog:image:flush"
  assert_output --partial "Catalog image cache flushed"
  assert [ "$status" -eq 0 ]

  run $BIN "cache:catalog:image:flush" --suppress-event
  assert_output --partial "Catalog image cache flushed"
  assert [ "$status" -eq 0 ]
}

# ============================================
# Command: cache:clean
# ============================================

@test "Command: cache:clean" {
	run $BIN "cache:clean" "layout"
	assert_output --partial "cleaned"
}

# ============================================
# Command: cache:disable
# ============================================

@test "Command: cache:disable" {
  run $BIN "cache:disable" "full_page"
	assert_output --partial The "following cache types were disabled"
}

# ============================================
# Command: cache:enable
# ============================================

@test "Command: cache:enable" {
  run $BIN "cache:enable" "full_page"
  assert_output --partial The "following cache types were enabled"
}

# ============================================
# Command: cache:flush
# ============================================

@test "Command: cache:flush" {
  run $BIN "cache:flush"
  assert_output --partial "cache flushed"
}

# ============================================
# Command: cache:report
# ============================================

@test "Command: cache:report" {
  run $BIN "cache:report"
  if [[ "$output" == *"The current cache adapter does not support getting all IDs."* ]]; then
    skip "The current cache adapter does not support getting all IDs."
  fi
  assert_output --partial "EXPIRE"
}

# ============================================
# Command: cache:remove:id
# ============================================

@test "Command: cache:remove:id" {
  run $BIN "cache:remove:id" "app_action_list"
  assert_output --partial "Cache entry with id app_action_list was removed"
}

@test "Command: cache:remove:id --strict" {
  run $BIN "cache:remove:id" --strict "not_exiting_cache_key_12345"
  assert [ "$status" -eq 1 ]
}

# ============================================
# Command: composer:redeploy-base-packages
# ============================================

@test "Command: composer:redeploy-base-packages" {
  run $BIN "composer:redeploy-base-packages"
  assert [ "$status" -eq 0 ]
  refute_output --partial "Calling PluginManager::addPlugin without \$sourcePackage is deprecated"
}

# ============================================
# Command: config:data:acl
# ============================================

@test "Command: config:data:acl" {
  run $BIN "config:data:acl"
  assert_output --partial "ACL Tree"
}

# ============================================
# Command: config:data:di
# ============================================

@test "Command: config:data:di" {
  run $BIN "config:data:di"
  assert_output --partial "DateTimeInterface"
}

# ============================================
# Command: config:data:mview
# ============================================

@test "Command: config:data:mview" {
  run $BIN "config:data:mview"
  assert_output --partial "catalog_category_flat"
}

@test "Command: config:data:mview with -t" {
  run $BIN "config:data:mview" -t
  assert_output --partial "MView Data Tree"
}

# ============================================
# Command: config:data:indexer
# ============================================

@test "Command: config:data:indexer" {
  run $BIN "config:data:indexer"
  assert_output --partial "catalog_product_flat"
}

@test "Command: config:data:indexer with -t" {
  run $BIN "config:data:indexer" -t
  assert_output --partial "Indexer Data Tree"
}

# ============================================
# Command: config:env:set
# ============================================

@test "Command: config:env:set" {
  run $BIN "config:env:set" magerun.example foo
  assert_output --partial "Config magerun.example successfully set to foo"
}

# ============================================
# Command: config:env:delete
# ============================================

@test "Command: config:env:delete" {
  run $BIN "config:env:delete" magerun.example
  assert_output --partial "Config magerun.example successfully removed"
}

# ============================================
# Command: config:env:show
# ============================================

@test "Command: config:env:show" {
  run $BIN "config:env:show"
  assert_output --partial "backend.frontName"
}

# ============================================
# Command: config:search
# ============================================

@test "Command: config:search" {
  run $BIN "config:search" "tax"
  assert_output --partial "Sales / Tax"
}

# ============================================
# Command: config:store:set
# ============================================

@test "Command: config:store:set" {
  run $BIN "config:store:set" n98/magerun/example defaultValue
  assert_output --partial "n98/magerun/example => defaultValue"
}

@test "Command: config:store:set with scope" {
  run $BIN "config:store:set" --scope=stores --scope-id=1 n98/magerun/example myStore2value
  assert_output --partial "n98/magerun/example => myStore2value"
}

# ============================================
# Command: config:store:get
# ============================================

@test "Command: config:store:get" {
  run $BIN "config:store:get" n98/magerun/example
  assert_output --partial "n98/magerun/example"
  assert_output --partial "defaultValue"
  assert_output --partial "myStore2value"
}

@test "Command: config:store:get with scope-id=1" {
  run $BIN "config:store:get" --scope=stores --scope-id=1 n98/magerun/example
  assert_output --partial "myStore2value"
}

@test "Command: config:store:get with scope-id=default" {
  run $BIN "config:store:get" --scope=stores --scope-id=default n98/magerun/example
  assert_output --partial "myStore2value"
}

@test "Command: config:store:get with scope-id=0" {
  run $BIN "config:store:get" --scope=stores --scope-id=0 n98/magerun/example
  assert_output --partial "n98/magerun/example"
}

@test "Command: config:store:get with scope-id=admin" {
  run $BIN "config:store:get" --scope=stores --scope-id=admin n98/magerun/example
  assert_output --partial "n98/magerun/example"
}

@test "Command: config:store:get with not existing scope-id" {
  run $BIN "config:store:get" --scope=stores --scope-id=not_existing n98/magerun/example
  assert [ "$status" -eq 1 ]
}

# ============================================
# Command: config:store:delete
# ============================================

@test "Command: config:store:delete" {
  run $BIN "config:store:delete" n98/magerun/example
  assert_output --partial "deleted path"
}

# ============================================
# Command: customer:create
# ============================================

@test "Command: customer:create" {
  $BIN "customer:delete" --email=foo@example.com --force >/dev/null 2>&1 || true

  run $BIN "customer:create" "foo@example.com" "Password123" "Firstname" "Lastname"
  assert_output --partial "Customer foo@example.com successfully created"
}

# ============================================
# Command: customer:info
# ============================================

@test "Command: customer:info" {
  run $BIN "customer:info" "foo@example.com"
  assert_output --partial "foo@example.com"
  assert_output --partial "Firstname"
  assert_output --partial "Lastname"
}

# ============================================
# Command: customer:list
# ============================================

@test "Command: customer:list" {
  run $BIN "customer:list"
  assert_output --partial "foo@example.com"
}

# ============================================
# Command: customer:change-password
# ============================================

@test "Command: customer:change-password" {
  run $BIN "customer:change-password" "foo@example.com" "Password1234"
  assert_output --partial "Password successfully changed"
}

# ============================================
# Command: customer:add-address
# ============================================

@test "Command: customer:add-address" {
  run $BIN "customer:add-address" foo@example.com base --firstname="John" --lastname="Doe" --street="Pariser Platz" --city="Berlin" --country="DE" --postcode="10117" --telephone="1234567890" --default-billing --default-shipping
  assert_output --partial "Address added successfully to customer foo@example.com"
}

# ============================================
# Command: customer:delete
# ============================================

@test "Command: customer:delete" {
  run $BIN "customer:delete" --fuzzy --email=foo --force
  assert_output --partial "Successfully deleted 1 customer/s"
}

# ============================================
# Command: db:add-default-authorization-entries
# ============================================

@test "Command: db:add-default-authorization-entries" {
  run $BIN "db:add-default-authorization-entries"
  assert_output --partial "OK"
}

# ============================================
# Command: db:dump
# ============================================

@test "Command: db:dump --stdout" {
  run $BIN "db:dump" --stdout
  assert [ "$status" -eq 0 ]
}

@test "Command: db:dump to file" {
  run $BIN "db:dump" "db.sql"
  assert_output --partial "Finished"
}

@test "Command: db:dump with --strip" {
  run $BIN "db:dump" --strip=@development db.sql
  assert_output --partial "Finished"
}

@test "Command: db:dump with --strip and --exclude" {
  run $BIN "db:dump" --strip=@development --exclude=core_config_data db.sql
  assert_output --partial "Finished"
}

@test "Command: db:dump with --strip and compression" {
  run $BIN "db:dump" --strip=@development --compression=gz db.sql
  assert_output --partial "Finished"
}

@test "Command: db:dump with --add-time option" {
  run $BIN "db:dump" --add-time=suffix db.sql
  [[ "$output" =~ Finished ]]
}

@test "Command: db:dump with --human-readable" {
  run $BIN "db:dump" --human-readable db.sql
  assert_output --partial "Finished"
}

@test "Command: db:dump with --git-friendly" {
  run $BIN "db:dump" --git-friendly db.sql
  assert_output --partial "Finished"
}

@test "Command: db:dump with --keep-definer" {
  run $BIN "db:dump" --keep-definer --only-command db.sql

  # check if the sed to replace the definer is not in the command pipe
  refute_output --partial "DEFINER"
}

@test "Command: db:dump --stdout with --strip" {
  run $BIN "db:dump" --stdout --strip=@development
  assert [ "$status" -eq 0 ]
}

@test "Command: db:dump with --strip and --only-command" {
  run $BIN "db:dump" --strip=@development --exclude=admin_* --only-command db.sql
  assert [ "$status" -eq 0 ]
  # first dump command should not contain excluded tables
  refute_output --regexp "--no-data=.*admin_user"

  # second dump command should ignore the table from data dump
  assert_output --regexp "--ignore-table=.*admin_user"
}

# ============================================
# Command: db:dump (mydumper)
# ============================================

@test "Command: db:dump --mydumper to directory" {
  if ! command -v mydumper &> /dev/null; then
    skip "mydumper not installed"
  fi
  run $BIN "db:dump" --mydumper db_mydumper.sql
  assert_output --partial "Finished"
  assert [ "$status" -eq 0 ]
}

@test "Command: db:dump --mydumper with --strip" {
  if ! command -v mydumper &> /dev/null; then
    skip "mydumper not installed"
  fi
  run $BIN "db:dump" --mydumper --strip=@development db_mydumper_strip.sql
  assert_output --partial "Finished"
  assert [ "$status" -eq 0 ]
}

@test "Command: db:dump --mydumper with --strip and --exclude" {
  if ! command -v mydumper &> /dev/null; then
    skip "mydumper not installed"
  fi
  if ! mydumper --help 2>&1 | grep -q -- '--ignore-table'; then
    skip "mydumper version does not support --ignore-table"
  fi
  run $BIN "db:dump" --mydumper --strip=@development --exclude=core_config_data db_mydumper_strip_excl.sql
  assert_output --partial "Finished"
  assert [ "$status" -eq 0 ]
}

@test "Command: db:dump --mydumper --only-command" {
  if ! command -v mydumper &> /dev/null; then
    skip "mydumper not installed"
  fi
  run $BIN "db:dump" --mydumper --only-command db_mydumper_cmd.sql
  assert [ "$status" -eq 0 ]
  assert_output --partial "mydumper"
}

@test "Command: db:dump --mydumper --only-command with --strip and --exclude" {
  if ! command -v mydumper &> /dev/null; then
    skip "mydumper not installed"
  fi
  run $BIN "db:dump" --mydumper --strip=@development --exclude=admin_* --only-command db_mydumper_strip_cmd.sql
  assert [ "$status" -eq 0 ]
  assert_output --partial "--no-data="
  assert_output --partial "--ignore-table="
}

# ============================================
# Command: db:info
# ============================================

@test "Command: db:info" {
  run $BIN "db:info"
  assert_output --partial "PDO-Connection-String"
}

# ============================================
# Command: db:status
# ============================================

@test "Command: db:status" {
  run $BIN "db:status"
  assert_output --partial "InnoDB Buffer Pool hit"
}

# ============================================
# Command: db:variables
# ============================================

@test "Command: db:variables" {
  run $BIN "db:variables"
  assert_output --partial "innodb_buffer_pool_size"
}


# ============================================
# Command: db:query
# ============================================

@test "Command: db:query --format=csv" {
  run $BIN "db:query" --format=csv "SELECT 1 AS foo, 2 AS bar"
  assert_output --partial '"foo","bar"'
  assert_output --partial '"1","2"'
  assert [ "$status" -eq 0 ]
}

# ============================================
# Command: design:demo-notice
# ============================================

@test "Command: design:demo-notice --on" {
  run $BIN "design:demo-notice" --on
  assert_output --partial "Demo Notice enabled for store default"
}

@test "Command: design:demo-notice --off" {
  run $BIN "design:demo-notice" --off
  assert_output --partial "Demo Notice disabled for store default"
}

#@test "Command: dev:console" {
#  run echo "dev:console --auto-exit 'ls'" | $BIN_INTERACTION
#  assert_output --partial "di"
#}

# ============================================
# Command: dev:module:create
# ============================================

@test "Command: dev:module:create" {
  cleanup_files_in_magento "app/code/Magerun123/TestModule"

  run $BIN "dev:module:create" Magerun123 TestModule
  assert_output --partial "Created directory"
  cleanup_files_in_magento "app/code/Magerun123/TestModule"
}

# ============================================
# Command: dev:di:preference:list
# ============================================

@test "Command: dev:di:preference:list" {
  run $BIN "dev:di:preference:list" global
  assert_output --partial "Magento\Store\Api\Data\StoreInterface"

  run $BIN "dev:di:preference:list" crontab
  assert_output --partial "Magento\Backend\App\ConfigInterface"
}

# ============================================
# Command: dev:module:detect-composer-dependencies
# ============================================

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

# ============================================
# Command: dev:module:list
# ============================================

@test "Command: dev:module:list" {
  run $BIN "dev:module:list"
  assert_output --partial "Magento_Store"
}

# ============================================
# Command: dev:module:observer:list
# ============================================

@test "Command: dev:module:observer:list" {
  run $BIN "dev:module:observer:list" sales_order_place_after global
  assert_output --partial "Observer name"
}

# ============================================
# Command: dev:translate:set
# ============================================

@test "Command: dev:translate:set" {
  run $BIN "dev:translate:set" foo foo_translate
  assert_output --partial "Translated"
}

@test "Command: dev:translate:set with default" {
  run $BIN "dev:translate:set" foo foo_translate default
  assert_output --partial "Translated"
}

# ============================================
# Command: dev:translate:admin
# ============================================

@test "Command: dev:translate:admin --on" {
  run $BIN "dev:translate:admin" --on
  assert_output --partial "enabled"
}

@test "Command: dev:translate:admin --off" {
  run $BIN "dev:translate:admin" --off
  assert_output --partial "disabled"
}

# ============================================
# Command: dev:translate:export
# ============================================

@test "Command: dev:translate:export" {
  run $BIN "dev:translate:export" en_US
  assert_output --partial "Exporting"
}

# ============================================
# Command: dev:translate:shop
# ============================================

@test "Command: dev:translate:shop --on" {
  run $BIN "dev:translate:shop" --on
  assert_output --partial "enabled"
}

@test "Command: dev:translate:shop --off" {
  run $BIN "dev:translate:shop" --off
  assert_output --partial "disabled"
}

# ============================================
# Command: dev:symlinks
# ============================================

@test "Command: dev:symlinks --on" {
  run $BIN "dev:symlinks" --on
  assert_output --partial "allowed"
}

@test "Command: dev:symlinks --off" {
  run $BIN "dev:symlinks" --off
  assert_output --partial "denied"
}

# ============================================
# Command: dev:template-hints
# ============================================

@test "Command: dev:template-hints --on" {
  run $BIN "dev:template-hints" --on
  assert_output --partial "enabled"
}

@test "Command: dev:template-hints --off" {
  run $BIN "dev:template-hints" --off
  assert_output --partial "disabled"
}

# ============================================
# Command: dev-template-hints-blocks
# ============================================

@test "Command: dev-template-hints-blocks --on" {
  run $BIN "dev:template-hints-blocks" --on
  assert_output --partial "enabled"
}

@test "Command: dev-template-hints-blocks --off" {
  run $BIN "dev:template-hints-blocks" --off
  assert_output --partial "disabled"
}

# ============================================
# Command: dev:theme:list
# ============================================

@test "Command: dev:theme:list" {
  run $BIN "dev:theme:list"
  assert_output --partial "Magento/backend"
}

# ============================================
# Command: dev:encrypt & dev:decrypt
# ============================================

@test "Command: dev:encrypt & dev:decrypt" {
  result=$($BIN "dev:encrypt" "testValue")
  run $BIN "dev:decrypt" "${result}"
  assert_output "testValue"
}

# ============================================
# Command: eav:attribute:list
# ============================================

@test "Command: eav:attribute:list" {
  run $BIN "eav:attribute:list"
  assert_output --partial "sku"
}

@test "Command: eav:attribute:view" {
  run $BIN "eav:attribute:view" catalog_product sku
  assert_output --partial "catalog_product_entity"
}

# ============================================
# Command: github:pr
# ============================================

@test "Command: github:pr 21787" {
  run $BIN "github:pr" 21787
  assert_output --partial "x_forwarded_for"
}

@test "Command: github:pr --patch 21787" {
  run $BIN "github:pr" --patch 21787
  assert_output --partial "PR-21787-magento-magento2.patch"
}

@test "Command: github:pr --diff 21787" {
  run $BIN "github:pr" --diff 21787
  assert_output --partial "setXForwardedFor"
}

@test "Command: github:pr --mage-os 1" {
  run $BIN "github:pr" --mage-os 1
  assert_output --partial "automatically"
}

@test "Command: github:pr --mage-os --patch 1" {
  run $BIN "github:pr" --mage-os --patch 1
  assert_output --partial "PR-1-mage-os-mageos-magento2.patch"
}

@test "Command: github:pr --mage-os --diff 1" {
  run $BIN "github:pr" --mage-os --diff 1
  assert_output --partial "server_url"
}

# ============================================
# Command: generation:flush
# ============================================

@test "Command: generation:flush" {
  run $BIN "generation:flush" Symfony
  assert_output --partial "Removed"
  assert_output --partial "Symfony"
}

# ============================================
# Command: indexer:list
# ============================================

@test "Command: indexer:list" {
  run $BIN "indexer:list"
  assert_output --partial "catalogsearch_fulltext"
}

# ============================================
# Command: indexer:trigger:recreate
# ============================================

@test "Command: indexer:trigger:recreate realtime" {
  run $BIN indexer:set-mode realtime catalog_product_price
  run $BIN "indexer:trigger:recreate"
  assert_output --partial 'Skipped indexer Product Price. Mode must be "schedule".'
}

@test "Command: indexer:trigger:recreate schedule" {
  run $BIN indexer:set-mode schedule catalog_product_price
  run $BIN "indexer:trigger:recreate"
  assert_output --partial "Re-created triggers of indexer Product Price"
}

# ============================================
# Command: Integration:create
# ============================================

@test "Command: Integration:create" {
  $BIN "integration:delete" magerun-test >/dev/null 2>&1 || true
  $BIN "integration:delete" magerun-test1 >/dev/null 2>&1 || true

  # Create with all arguments
  run $BIN "integration:create" magerun-test magerun@example.com https://localhost
  assert_output --partial "Integration ID"

  # Create with minimal arguments
  run $BIN "integration:create" magerun-test1
  assert_output --partial "Integration ID"
}

# ============================================
# Command: integration:list
# ============================================

@test "Command: integration:list" {
  run $BIN "integration:list"
  assert_output --partial "magerun-test"
}

# ============================================
# Command: integration:show
# ============================================

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

# ============================================
# Command: integration:delete
# ============================================

@test "Command: integration:delete" {
  run $BIN "integration:delete" magerun-test
  assert_output --partial "Successfully deleted integration"

  $BIN "integration:delete" magerun-test1 >/dev/null 2>&1 || true
}

# ============================================
# Command: magerun:config:dump
# ============================================

@test "Command: magerun:config:dump" {
  run $BIN "magerun:config:dump" --only-dist
  assert_output --partial "commands:"
  assert_output --partial "check-root-user:"
}

# ============================================
# Command: magerun:config:info
# ============================================

@test "Command: magerun:config:info" {
  run $BIN "magerun:config:info"
  assert_output --partial "type"
  assert_output --partial "dist"
}

# ============================================
# Command: media:dump
# ============================================

@test "Command: media:dump" {
  run $BIN "media:dump"
  assert_output --partial "Compress"
}

# ============================================
# Command: route:list
# ============================================

@test "Command: route:list -m Magento_Backend -a adminhtml" {
  run $BIN "route:list" -m Magento_Backend -a adminhtml
  assert_output --partial "admin/dashboard/index"
  assert_output --partial "GET,POST"
}

@test "Command: route:list -m Magento_Multishipping -a frontend" {
  run $BIN "route:list" -m Magento_Multishipping -a frontend
  assert_output --partial "multishipping/checkout_address/editaddress"
  assert_output --partial "GET,POST"
}

# ============================================
# Command: script:repo:list
# ============================================

@test "Command: script:repo:list" {
  run $BIN "script:repo:list"
  assert_output --partial "Script"
}

# ============================================
# Command: search:engine:list
# ============================================

@test "Command: search:engine:list" {
  run $BIN "search:engine:list"
  assert_output --partial "label"
}

# ============================================
# Command: sys:check
# ============================================

@test "Command: sys:check" {
  run $BIN "sys:check"
  assert_output --partial "Env"
}

# ============================================
# Command: sys:cron:kill
# ============================================

@test "Command: sys:cron:kill" {
  run $BIN "sys:cron:kill" "not_exiting_job_code"
  assert_output --partial "No process found to kill"
}

# ============================================
# Command: sys:cron:list
# ============================================

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

@test "Command: sys:email:test missing --to" {
  # --no-interaction avoids blocking on the interactive prompt and keeps this from ever
  # reaching the real mail transport.
  run $BIN "sys:email:test" --no-interaction
  assert_output --partial "Please provide a valid recipient email address with --to"
  assert [ "$status" -eq 1 ]
}

@test "Command: sys:email:test invalid --to" {
  run $BIN "sys:email:test" --to=not-an-email --no-interaction
  assert_output --partial "Please provide a valid recipient email address with --to"
  assert [ "$status" -eq 1 ]
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

@test "Command: sys:store:config:base-url:list default" {
  run $BIN "sys:store:config:base-url:list"
  assert_output --partial "unsecure_baseurl"
}

@test "Command: sys:store:config:base-url:list --with-admin-store" {
  run $BIN "sys:store:config:base-url:list --with-admin-store"
  assert_output --partial "admin"
}

@test "Command: sys:store:config:base-url:list --with-admin-admin-login-url" {
  run $BIN "sys:store:config:base-url:list --with-admin-admin-login-url"
  assert_output --partial "admin"
}

@test "Command: sys:store:config:base-url:list validation error" {
  run $BIN "sys:store:config:base-url:list --with-admin-store --with-admin-admin-login-url"
  assert [ "$status" -eq 1 ]
}

@test "Command: sys:store:list" {
  run $BIN "sys:store:list"
  assert_output --partial "default"
}

@test "Command: sys:store:create and sys:store:delete" {
  store_code="bats_store_$(date +%s%N)"
  store_name="Bats Store"

  run $BIN "sys:store:create" "$store_code" "$store_name" --group-id=1
  store_id=$(printf '%s\n' "$output" | extract_store_id)

  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created store"
  assert_output --partial "$store_code"

  assert [ -n "$store_id" ]

  run $BIN "sys:store:delete" "$store_id" --force
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully deleted store"
  assert_output --partial "$store_code"
}

@test "Command: sys:store:create prompts for every required parameter" {
  store_code="bats_interactive_store_$(date +%s%N)"
  store_name="Bats Interactive Store"

  run bash -c "printf '%s\\n%s\\n1\\n' \"$store_code\" \"$store_name\" | $BIN_INTERACTION sys:store:create"
  store_id=$(printf '%s\n' "$output" | extract_store_id)

  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created store"
  assert_output --partial "$store_code"

  assert [ -n "$store_id" ]

  run $BIN "sys:store:delete" "$store_id" --force
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:store:delete prompts for confirmation and selection" {
  store_code="bats_select_store_$(date +%s%N)"
  store_name="Bats Select Store"

  run $BIN "sys:store:create" "$store_code" "$store_name" --group-id=1
  store_id=$(printf '%s\n' "$output" | extract_store_id)

  assert [ "$status" -eq 0 ]
  assert [ -n "$store_id" ]

  run bash -c "printf '%s\\ny\\n' \"$store_id\" | $BIN_INTERACTION sys:store:delete"
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully deleted store"
  assert_output --partial "ID: $store_id"
}

@test "Command: sys:store:create rejects invalid store code" {
  run $BIN "sys:store:create" "1invalid" "Invalid Store" --group-id=1
  assert [ "$status" -ne 0 ]
  assert_output --partial "Store code may only contain letters"
}

@test "Command: sys:store:delete rejects the default store" {
  run $BIN "sys:store:list" --format=csv
  assert [ "$status" -eq 0 ]

  default_store_id=$(printf '%s\n' "$output" | awk -F, '$1 == "1" { print $1; exit }')
  assert [ -n "$default_store_id" ]

  run $BIN "sys:store:delete" "$default_store_id" --force
  assert [ "$status" -ne 0 ]
  assert_output --partial "default store"
}

@test "Command: sys:url:list" {
  run $BIN "sys:url:list" --add-cmspages default '{host},{path}'
  assert_output --partial "/"
}

@test "Command: sys:url:regenerate --products 1 --categories 2 --store 1" {
  run $BIN sys:url:regenerate
  assert_output --partial "Generated"
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:url:regenerate --all-products" {
  run $BIN sys:url:regenerate
  assert_output --partial "Generated"
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:url:regenerate --all-categories" {
  run $BIN sys:url:regenerate
  assert_output --partial "Generated"
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:url:regenerate --all-cms-pages" {
  run $BIN sys:url:regenerate
  assert_output --partial "Generated"
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:website:list" {
  run $BIN "sys:website:list"
  assert_output --partial "base"

  run $BIN "sys:website:list" --format=csv
  assert_output --partial "1,base"
}

@test "Command: sys:website:create and sys:website:delete" {
  website_code="bats_website_$(date +%s%N)"
  website_name="Bats Website"

  run $BIN "sys:website:create" "$website_code" "$website_name"
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created website"
  assert_output --partial "$website_code"

  run $BIN "sys:website:delete" "$website_code" --force
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully deleted website"
  assert_output --partial "$website_code"
}

@test "Command: sys:website:create prompts for every required parameter" {
  website_code="bats_interactive_website_$(date +%s%N)"
  website_name="Bats Interactive Website"

  run bash -c "printf '%s\\n%s\\n' \"$website_code\" \"$website_name\" | $BIN_INTERACTION sys:website:create"
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created website"
  assert_output --partial "$website_code"

  run $BIN "sys:website:delete" "$website_code" --force
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:website:create rejects invalid website code" {
  run $BIN "sys:website:create" "1invalid" "Invalid Website"
  assert [ "$status" -ne 0 ]
  assert_output --partial "Website code may only contain letters"
}

@test "Command: sys:store-group:create and sys:store-group:delete" {
  group_name="Bats Store Group $(date +%s%N)"
  group_code="bats_store_group_$(date +%s%N)"

  run $BIN "sys:store-group:create" "$group_code" "$group_name" --website-id=1 --root-category-id=2
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created store group"
  assert_output --partial "$group_name"

  group_id=$(printf '%s\n' "$output" | sed -n 's/.*ID: \([0-9][0-9]*\).*/\1/p')
  assert [ -n "$group_id" ]

  run $BIN "sys:store-group:delete" "$group_id" --force
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully deleted store group"
  assert_output --partial "$group_name"
}

@test "Command: sys:store-group:create prompts for missing parameters" {
  group_name="Bats Interactive Store Group $(date +%s%N)"
  group_code="bats_interactive_group_$(date +%s%N)"

  run bash -c "printf '%s\\n%s\\nbase\\n2\\n' \"$group_code\" \"$group_name\" | $BIN_INTERACTION sys:store-group:create"
  assert [ "$status" -eq 0 ]
  assert_output --partial "Successfully created store group"
  assert_output --partial "$group_name"

  group_id=$(printf '%s\n' "$output" | sed -n 's/.*ID: \([0-9][0-9]*\).*/\1/p' | sed -n '$p')
  assert [ -n "$group_id" ]

  run $BIN "sys:store-group:delete" "$group_id" --force
  assert [ "$status" -eq 0 ]
}

@test "Command: sys:store-group:create validates website and root category" {
  run $BIN "sys:store-group:create" invalid_group "Invalid Store Group" --website-id=999999 --root-category-id=2
  assert [ "$status" -ne 0 ]
  assert_output --partial "does not exist"

  run $BIN "sys:store-group:create" invalid_group "Invalid Store Group" --website-id=1 --root-category-id=0
  assert [ "$status" -ne 0 ]
  assert_output --partial "root category ID must be a positive integer"
}

@test "Command: sys:store-group:delete rejects the default store group" {
  run $BIN "sys:store:list" --format=csv
  assert [ "$status" -eq 0 ]

  default_group_id=$(printf '%s\n' "$output" | awk -F, '$2 == "1" { print $3; exit }')
  assert [ -n "$default_group_id" ]

  run $BIN "sys:store-group:delete" "$default_group_id" --force
  assert [ "$status" -ne 0 ]
  assert_output --partial "The default store group cannot be deleted"
}


# ============================================
# Command: dev: keep-calm
# ============================================

@test "Command: dev:keep-calm" {
  run $BIN "dev:keep-calm" --force-static-content-deploy
  assert [ "$status" -eq 0 ]
}

# ============================================
# Command: dev:log:size
# ============================================

@test "Command: dev:log:size" {
  run $BIN "dev:log:size"
  assert_output --partial "Total:"
  assert [ "$status" -eq 0 ]
}

@test "Command: dev:log:size --human-readable" {
  run $BIN "dev:log:size" --human-readable
  assert_output --partial "Total:"
  assert [ "$status" -eq 0 ]
}

@test "Command: dev:log:size --sort-by-size" {
  run $BIN "dev:log:size" --sort-by-size
  assert_output --partial "Total:"
  assert [ "$status" -eq 0 ]
}

@test "Command: dev:log:size --filter system" {
  run $BIN "dev:log:size" --filter system
  assert_output --partial "Total:"
  assert [ "$status" -eq 0 ]
}
