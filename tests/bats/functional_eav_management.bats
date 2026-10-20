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

# Return the displayed ordinal, never the record ID, for interactive menus.
menu_choice() {
    php -r '
        require getenv("N98_MAGERUN2_TEST_MAGENTO_ROOT") . "/app/bootstrap.php";
        $om = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER)->getObjectManager();
        $resource = $om->get(\Magento\Framework\App\ResourceConnection::class);
        $db = $resource->getConnection();
        if ($argv[1] === "entity") {
            $values = $db->fetchCol($db->select()->from($resource->getTableName("eav_entity_type"), "entity_type_code")->order("entity_type_code ASC"));
            $target = $argv[2];
        } elseif ($argv[1] === "set") {
            $entity = $om->get(\Magento\Eav\Model\Config::class)->getEntityType("catalog_product")->getId();
            $values = $db->fetchCol($db->select()->from($resource->getTableName("eav_attribute_set"), "attribute_set_id")->where("entity_type_id = ?", $entity)->order("attribute_set_id ASC"));
            $target = $argv[2];
        } else {
            $values = $db->fetchCol($db->select()->from($resource->getTableName("eav_attribute_group"), "attribute_group_id")->where("attribute_set_id = ?", $argv[2])->order(["sort_order ASC", "attribute_group_id ASC"]));
            $target = $argv[3];
        }
        $index = array_search($target, array_map("strval", $values), true);
        if ($index === false) { throw new \RuntimeException("Fixture is not a valid choice"); }
        echo $index + 1;
    ' "$@"
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
        refute_output --partial 'Please select an entity type'
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
    refute_output --partial 'Please select an attribute group'
}

@test "All five EAV management commands collect only missing required inputs" {
    read -r set_id default_group_id <<< "$(product_context)"
    suffix="$(date +%s)_${RANDOM}"
    attribute_code="n98_bats_${suffix}"
    group_name="Magerun BATS interactive ${suffix}"
    entity_choice=$(menu_choice entity catalog_product)
    set_choice=$(menu_choice set "$set_id")
    run bash -c 'printf "%s\n" "$1" "$2" "$3" | $BIN_INTERACTION eav:attribute-group:create' _ "$entity_choice" "$set_choice" "$group_name"
    assert_success
    group_id=$(printf '%s\n' "$output" | sed -n 's/.*Created attribute group ID: \([0-9][0-9]*\).*/\1/p')
    [[ "$group_id" =~ ^[1-9][0-9]*$ ]]
    group_choice=$(menu_choice group "$set_id" "$group_id")
    refute_output --partial 'Sort order'

    run bash -c 'printf "%s\n" "$1" "Interactive BATS label" | $BIN_INTERACTION eav:attribute:create catalog_product --attribute-set="$2" --attribute-group="$3" --input=boolean --default=0' _ "$attribute_code" "$set_id" "$group_id"
    assert_success
    refute_output --partial 'Please select an entity type'
    refute_output --partial 'Scope'
    run bash -c 'printf "%s\n" "$1" | $BIN_INTERACTION eav:attribute-group:list catalog_product' _ "$set_choice"
    assert_success
    assert_output --partial "$group_name"
    refute_output --partial 'Please select an entity type'
    run bash -c 'printf "%s\n" "$2" | $BIN_INTERACTION eav:attribute-group:view catalog_product "$1"' _ "$set_id" "$group_choice"
    assert_success
    assert_output --partial "$attribute_code"
    refute_output --partial 'Please select an attribute set'
    # Capture stdout and stderr independently: prompts must not enter the document.
    for format in json csv; do
        stdout_file=$(mktemp)
        stderr_file=$(mktemp)
        run bash -c 'printf "%s\n" "$3" | $BIN_INTERACTION eav:attribute-group:view catalog_product "$2" --format="$1" >"$4" 2>"$5"' _ "$format" "$set_id" "$group_choice" "$stdout_file" "$stderr_file"
        assert_success
        run grep -F 'Please select an attribute group' "$stderr_file"
        assert_success
        run php -r '
            if ($argv[1] === "json") {
                $rows = json_decode(file_get_contents($argv[2]), true, 512, JSON_THROW_ON_ERROR);
            } else {
                $stream = fopen($argv[2], "r");
                $headers = fgetcsv($stream, 0, ",", "\"", "");
                if ($headers !== ["property", "value"]) { exit(1); }
                $rows = [];
                while (($row = fgetcsv($stream, 0, ",", "\"", "")) !== false) {
                    $rows[] = array_combine($headers, $row);
                }
            }
            if (!in_array("attribute." . $argv[3], array_column($rows, "property"), true)) { exit(1); }
        ' "$format" "$stdout_file" "$attribute_code"
        assert_success
        rm "$stdout_file" "$stderr_file"
    done
    run $BIN eav:attribute:remove catalog_product "$attribute_code"
    assert_success

    run bash -c 'printf "%s\n" "$2" no | $BIN_INTERACTION eav:attribute-group:delete catalog_product "$1"' _ "$set_id" "$group_choice"
    assert_failure
    assert_output --partial 'Deletion cancelled'
    run bash -c 'printf "%s\n" "$2" yes | $BIN_INTERACTION eav:attribute-group:delete catalog_product "$1"' _ "$set_id" "$group_choice"
    assert_success
    group_id=''
    attribute_code=''
}

@test "Group list formats keep stdout clean and unknown formats fail on stderr" {
    read -r set_id default_group_id <<< "$(product_context)"
    set_choice=$(menu_choice set "$set_id")
    for format in json csv; do
        stdout_file=$(mktemp)
        stderr_file=$(mktemp)
        run bash -c 'printf "%s\n" "$2" | $BIN_INTERACTION eav:attribute-group:list catalog_product --format="$1" >"$3" 2>"$4"' _ "$format" "$set_choice" "$stdout_file" "$stderr_file"
        assert_success
        run grep -F 'Please select an attribute set' "$stderr_file"
        assert_success
        run php -r '
            if ($argv[1] === "json") {
                $rows = json_decode(file_get_contents($argv[2]), true, 512, JSON_THROW_ON_ERROR);
                if (!$rows || !isset(reset($rows)["attribute_set_id"])) { exit(1); }
            } else {
                $stream = fopen($argv[2], "r");
                if (fgetcsv($stream, 0, ",", "\"", "") !== ["id", "name", "attribute_set_id", "sort_order", "default_id"]) { exit(1); }
                if (count(fgetcsv($stream, 0, ",", "\"", "")) !== 5) { exit(1); }
            }
        ' "$format" "$stdout_file"
        assert_success
        run bash -c '$BIN eav:attribute-group:list catalog_product "$1" --format=invalid >"$2" 2>"$3"' _ "$set_id" "$stdout_file" "$stderr_file"
        assert_failure
        [[ ! -s "$stdout_file" ]]
        run grep -F 'Unknown output format' "$stderr_file"
        assert_success
        rm "$stdout_file" "$stderr_file"
    done
    run $BIN eav:attribute-group:list catalog_product "$set_id" --format
    assert_success
    assert_output --partial 'attribute_set_id'
    run $BIN eav:attribute-group:list catalog_product "$set_id" --format=
    assert_failure
    assert_output --partial 'Unknown output format'
}
