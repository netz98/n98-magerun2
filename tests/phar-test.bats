setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'

    export ADDITIONAL_OPTIONS="";
    export PHP_BIN="php"

    if [ -z "$TEST_PHAR_FILE" ]; then
        echo "ENV variable \$TEST_PHAR_FILE is not set.";
        exit 1;
    fi

    if [ -z "TEST_MAGENTO_ROOT_DIR" ]; then
        echo "ENV variable \$TEST_MAGENTO_ROOT_DIR is not set.";
        exit 1;
    fi

    export TESTS_WITH_ERRORS=false;
    export MAGERUN_CMD="${PHP_BIN} -f ${TEST_PHAR_FILE} -- --no-interaction --root-dir=${TEST_MAGENTO_ROOT_DIR}"
}

@test "test admin:user:list" {
	run $MAGERUN_CMD "admin:user:list"
	assert_output --partial "username"
}

@test "test cache:clean" {
	run $MAGERUN_CMD "cache:clean" "layout"
	assert_output --partial "cleaned"
}

@test "test cache:disable" {
    run $MAGERUN_CMD "cache:disable" "full_page"
	assert_output --partial The "following cache types were disabled"
}

