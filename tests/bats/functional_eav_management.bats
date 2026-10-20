#!/usr/bin/env bats

# Run only against an isolated disposable Magento installation.
setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'
    : "${N98_MAGERUN2_BIN:?Set N98_MAGERUN2_BIN}"
    : "${N98_MAGERUN2_TEST_MAGENTO_ROOT:?Set an isolated Magento root}"
    export BIN="$(command -v php) -f ${N98_MAGERUN2_BIN} -- --no-interaction --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

teardown() {
    if [ -n "${attribute_code:-}" ]; then
        $BIN eav:attribute:remove catalog_product "$attribute_code" >/dev/null 2>&1 || true
    fi
    if [ -n "${group_id:-}" ] && [ -n "${set_id:-}" ]; then
        $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id" --force >/dev/null 2>&1 || true
    fi
}

@test "Product attribute and group lifecycle preserves default and populated groups" {
    # Discover the actual installation-specific default set through Magento, not a hardcoded ID.
    context=$(php -r '
        require getenv("N98_MAGERUN2_TEST_MAGENTO_ROOT") . "/app/bootstrap.php";
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
        $setup = $bootstrap->getObjectManager()->get(\Magento\Eav\Setup\EavSetupFactory::class)->create();
        $setId = $setup->getDefaultAttributeSetId("catalog_product");
        echo $setId . " " . $setup->getDefaultAttributeGroupId("catalog_product", $setId);
    ')
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

    run $BIN eav:attribute-group:create catalog_product "$set_id" "$group_name" --sort-order=99
    assert_success
    group_id=$(printf '%s\n' "$output" | sed -n 's/.*Created attribute group ID: \([0-9][0-9]*\).*/\1/p')
    [[ "$group_id" =~ ^[1-9][0-9]*$ ]]
    run $BIN eav:attribute-group:list catalog_product "$set_id" --format=csv
    assert_success
    assert_output --partial "$group_name"
    run $BIN eav:attribute-group:create catalog_product "$set_id" "$group_name"
    assert_failure
    assert_output --partial 'already exists'

    run $BIN eav:attribute-group:delete catalog_product "$set_id" "$group_id"
    assert_failure
    assert_output --partial 'cancelled'
    run $BIN eav:attribute:create catalog_product "$attribute_code" --label='BATS example' \
        --input=boolean --default=0 --attribute-set="$set_id" --attribute-group="$group_id"
    assert_success
    run $BIN eav:attribute:view catalog_product "$attribute_code"
    assert_success
    assert_output --partial 'BATS example'
    run $BIN eav:attribute-group:view catalog_product "$set_id" "$group_id" --format=json
    assert_success
    assert_output --partial "$attribute_code"
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
