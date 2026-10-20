#!/usr/bin/env bats

# Run only against an isolated disposable Magento installation.
setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'
    : "${N98_MAGERUN2_BIN:?Set N98_MAGERUN2_BIN}"
    : "${N98_MAGERUN2_TEST_MAGENTO_ROOT:?Set an isolated Magento root}"
    export BIN="$(command -v php) -f ${N98_MAGERUN2_BIN} -- --no-interaction --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
    export BIN_INTERACTION="$(command -v php) -f ${N98_MAGERUN2_BIN} -- --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

teardown() {
    if [ -n "${attribute_code:-}" ]; then
        $BIN eav:attribute:remove catalog_product "$attribute_code" >/dev/null 2>&1 || true
    fi
    if [ -n "${group_id:-}" ] && [ -n "${set_id:-}" ]; then
        $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id" --force >/dev/null 2>&1 || true
    fi
}

product_context() {
    php -r '
        require getenv("N98_MAGERUN2_TEST_MAGENTO_ROOT") . "/app/bootstrap.php";
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
        $setup = $bootstrap->getObjectManager()->get(\Magento\Eav\Setup\EavSetupFactory::class)->create();
        $setId = $setup->getDefaultAttributeSetId("catalog_product");
        echo $setId . " " . $setup->getDefaultAttributeGroupId("catalog_product", $setId);
    '
}

@test "Product attribute and group lifecycle preserves default and populated groups" {
    # Discover the actual installation-specific default set through Magento, not a hardcoded ID.
    context=$(product_context)
    read -r set_id default_group_id <<< "$context"
    [[ "$set_id" =~ ^[1-9][0-9]*$ ]]
    [[ "$default_group_id" =~ ^[1-9][0-9]*$ ]]
    run $BIN eav:attribute-group:delete catalog_product "$set_id" "$default_group_id" --force
    assert_failure
    assert_output --partial 'default'
    run $BIN eav:attribute-group:create catalog_product "$set_id" 123
    assert_failure
    assert_output --partial 'non-numeric'
    suffix="$(date +%s)_${RANDOM}"
    attribute_code="n98_bats_${suffix}"
    group_name="Magerun BATS ${suffix}"

    run $BIN eav:attribute-group:create catalog_product "$set_id" "$group_name" --sort-order=32768
    group_id=$(printf '%s\n' "$output" | sed -n 's/.*Created attribute group ID: \([0-9][0-9]*\).*/\1/p')
    assert_failure
    assert_output --partial 'between 0 and 32767'
    run $BIN eav:attribute-group:list catalog_product "$set_id" --format=csv
    assert_success
    refute_output --partial "$group_name"
    run $BIN eav:attribute-group:create catalog_product "$set_id" "$group_name" --sort-order=32767
    assert_success
    group_id=$(printf '%s\n' "$output" | sed -n 's/.*Created attribute group ID: \([0-9][0-9]*\).*/\1/p')
    [[ "$group_id" =~ ^[1-9][0-9]*$ ]]
    run $BIN eav:attribute-group:list catalog_product "$set_id" --format=csv
    assert_success
    assert_output --partial "$group_name"
    assert_output --partial '32767'
    run $BIN eav:attribute-group:create catalog_product "$set_id" "$group_name"
    assert_failure
    assert_output --partial 'already exists'

    run $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id"
    assert_failure
    assert_output --partial 'cancelled'
    run $BIN eav:attribute:create catalog_product "$attribute_code" --label='BATS example' --sort-order=32768
    assert_failure
    assert_output --partial 'between 0 and 32767'
    run $BIN eav:attribute:view catalog_product "$attribute_code"
    assert_failure
    assert_output --partial 'not found'
    run $BIN eav:attribute:create catalog_product "$attribute_code" --label='BATS example' \
        --input=boolean --default=0 --sort-order=32767 --attribute-set="$set_id" --attribute-group="$group_id"
    assert_success
    run $BIN eav:attribute:view catalog_product "$attribute_code"
    assert_success
    assert_output --partial 'BATS example'
    run $BIN eav:attribute-group:view catalog_product "$set_id" "$group_id" --format=json
    assert_success
    assert_output --partial "$attribute_code"
    assert_output --partial 'sort order: 32767'
    run $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id" --force
    assert_failure
    assert_output --partial 'populated'
    run $BIN eav:attribute:create catalog_product "$attribute_code" --label='Must not replace'
    assert_failure
    assert_output --partial 'already exists'
    run $BIN eav:attribute:remove catalog_product "$attribute_code"
    assert_success
    run $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id" --force
    assert_success
    run $BIN eav:attribute-group:view catalog_product "$set_id" "$group_id"
    assert_failure
    assert_output --partial 'not found'
    group_id=''
    attribute_code=''
}

@test "EAV management commands are registered and have explicit context" {
    for command in eav:attribute:create eav:attribute-group:create eav:attribute-group:list eav:attribute-group:delete eav:attribute-group:view; do
        run $BIN help "$command"
        assert_success
        assert_output --partial 'entityType'
    done
    run $BIN help eav:attribute-group:delete
    assert_success
    assert_output --partial 'attributeSetId'
    assert_output --partial 'attributeGroupId'
    assert_output --partial '--force'
}

@test "EAV group commands fail for missing entity and invalid set without mutation" {
    run $BIN eav:attribute-group:list n98_missing_entity 1
    assert_failure
    run $BIN eav:attribute-group:create catalog_product 0 'Must not be created'
    assert_failure
    assert_output --partial 'positive numeric ID'
    run $BIN eav:attribute-group:delete catalog_product 2147483647 2147483647 --force
    assert_failure
    assert_output --partial 'not found'
}

@test "EAV attribute creation rejects malformed metadata and PHP class options" {
    run $BIN eav:attribute:create catalog_product invalid-code --label Example
    assert_failure
    assert_output --partial 'Attribute code'
    run $BIN eav:attribute:create catalog_product n98_invalid_metadata --label Example --required yes
    assert_failure
    assert_output --partial 'required must be 0 or 1'
    run $BIN eav:attribute:create catalog_product n98_invalid_metadata --label Example --backend 'Arbitrary\Class'
    assert_failure
    assert_output --partial 'does not exist'
}

@test "All EAV management commands fail clearly for missing noninteractive inputs" {
    for command in eav:attribute:create eav:attribute-group:create eav:attribute-group:list eav:attribute-group:delete eav:attribute-group:view; do
        run $BIN "$command"
        assert_failure
        assert_output --partial 'Not enough arguments'
        refute_output --partial 'Entity type (enter'
    done
    attribute_code="n98_missing_${RANDOM}"
    run $BIN eav:attribute:create catalog_product "$attribute_code"
    assert_failure
    assert_output --partial 'Missing required option --label'
    run $BIN eav:attribute:view catalog_product "$attribute_code"
    assert_failure
    assert_output --partial 'not found'
}

@test "Interactive EAV selection cancellation, EOF and invalid supplied context are safe" {
    run bash -c 'printf "\n" | $BIN_INTERACTION eav:attribute-group:create'
    assert_failure
    assert_output --partial 'cancelled'
    refute_output --partial 'Created attribute group'
    run bash -c '$BIN_INTERACTION eav:attribute-group:create </dev/null'
    assert_failure
    assert_output --partial 'cancelled'
    run bash -c '$BIN_INTERACTION eav:attribute-group:view catalog_product 2147483647 </dev/null'
    assert_failure
    assert_output --partial 'not found'
    refute_output --partial 'Attribute group (enter'
}

@test "All five EAV management commands collect only missing required inputs" {
    read -r set_id default_group_id <<< "$(product_context)"
    suffix="$(date +%s)_${RANDOM}"
    attribute_code="n98_bats_${suffix}"
    group_name="Magerun BATS interactive ${suffix}"
    run bash -c 'printf "%s\n" catalog_product "$1" "$2" | $BIN_INTERACTION eav:attribute-group:create' _ "$set_id" "$group_name"
    assert_success
    group_id=$(printf '%s\n' "$output" | sed -n 's/.*Created attribute group ID: \([0-9][0-9]*\).*/\1/p')
    [[ "$group_id" =~ ^[1-9][0-9]*$ ]]
    refute_output --partial 'Sort order'

    run bash -c 'printf "%s\n" "$1" "Interactive BATS label" | $BIN_INTERACTION eav:attribute:create catalog_product --attribute-set="$2" --attribute-group="$3" --input=boolean --default=0' _ "$attribute_code" "$set_id" "$group_id"
    assert_success
    refute_output --partial 'Entity type (enter'
    refute_output --partial 'Scope'
    run bash -c 'printf "%s\n" "$1" | $BIN_INTERACTION eav:attribute-group:list catalog_product' _ "$set_id"
    assert_success
    assert_output --partial "$group_name"
    refute_output --partial 'Entity type (enter'
    run bash -c 'printf "%s\n" "$2" | $BIN_INTERACTION eav:attribute-group:view catalog_product "$1"' _ "$set_id" "$group_id"
    assert_success
    assert_output --partial "$attribute_code"
    refute_output --partial 'Attribute set (enter'
    run $BIN eav:attribute:remove catalog_product "$attribute_code"
    assert_success

    run bash -c 'printf "%s\n" "$2" no | $BIN_INTERACTION eav:attribute-group:delete catalog_product "$1"' _ "$set_id" "$group_id"
    assert_failure
    assert_output --partial 'Deletion cancelled'
    run bash -c 'printf "%s\n" "$2" yes | $BIN_INTERACTION eav:attribute-group:delete catalog_product "$1"' _ "$set_id" "$group_id"
    assert_success
    group_id=''
    attribute_code=''
}
