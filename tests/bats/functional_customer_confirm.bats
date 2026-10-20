#!/usr/bin/env bats

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'
    : "${N98_MAGERUN2_BIN:?Set N98_MAGERUN2_BIN}"
    : "${N98_MAGERUN2_TEST_MAGENTO_ROOT:?Set N98_MAGERUN2_TEST_MAGENTO_ROOT}"
    BIN="php -f ${N98_MAGERUN2_BIN} -- --no-interaction --root-dir=${N98_MAGERUN2_TEST_MAGENTO_ROOT}"
}

teardown() {
    if [ -n "${email:-}" ]; then
        $BIN customer:delete --email="$email" --force >/dev/null 2>&1
    fi
}

@test "customer:confirm is registered with --resend" {
    run $BIN customer:confirm --help
    assert_success
    assert_output --partial '--resend'
}

@test "customer:confirm requires an email non-interactively" {
    run $BIN customer:confirm
    assert_failure
    assert_output --partial 'An email is required'
}

@test "customer:confirm rejects malformed and whitespace emails without prompting in either mode" {
    for email_value in 'malformed-email' '   '; do
        for option in '' '--resend'; do
            # Bound the process so a regression to an interactive prompt cannot hang BATS.
            run timeout --foreground -k 2s 5s $BIN customer:confirm "$email_value" base $option
            assert_equal "$status" 1
            assert_output --partial 'A valid email address is required'
            refute_output --partial 'Email:'
        done
    done
}

@test "customer:confirm rejects missing customers in both modes" {
    for option in '' '--resend'; do
        run $BIN customer:confirm "missing-confirm-${BATS_TEST_NUMBER}-$$@example.invalid" base $option
        assert_failure
        assert_output --partial 'was not found'
    done
}

@test "customer:confirm never unconfirms an already confirmed customer" {
    email="confirmed-${BATS_TEST_NUMBER}-$$@example.com"
    run $BIN customer:create "$email" 'Confirm-Passw0rd!' Confirm Test base
    assert_success
    for option in '' '--resend'; do
        run $BIN customer:confirm "$email" base $option
        assert_failure
        assert_output --partial 'already confirmed'
    done
    run $BIN customer:delete --email="$email" --force
    assert_success
}

@test "customer:confirm resends without confirming then confirms a pending account" {
    email="pending-${BATS_TEST_NUMBER}-$$@example.com"
    run $BIN customer:create "$email" 'Confirm-Passw0rd!' Confirm Test base
    assert_success
    run $BIN db:query "UPDATE customer_entity SET confirmation = 'bats-pending-key' WHERE email = '$email'"
    assert_success
    run $BIN customer:confirm "$email" base --resend
    assert_success
    assert_output --partial 'Account remains pending'
    run $BIN db:query "SELECT COUNT(*) AS still_pending FROM customer_entity WHERE email = '$email' AND confirmation = 'bats-pending-key'" --format=csv
    assert_success
    assert_output --partial '1'
    run $BIN customer:confirm "$email" base
    assert_success
    assert_output --partial 'Customer account successfully confirmed'
    run $BIN customer:confirm "$email" base --resend
    assert_failure
    assert_output --partial 'already confirmed'
}
