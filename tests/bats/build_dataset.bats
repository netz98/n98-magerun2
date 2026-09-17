#!/usr/bin/env bats
#
# Exercises the db-compatibility dataset refresh logic added to build.sh (validate / fetch /
# fallback / --skip-dataset-fetch). Runs against a throwaway copy of the repo's PHP validator
# and dataset files plus a local PHP built-in server, so it never touches the real
# ./res/db-compatibility.json and never runs the (heavy, network-dependent) full build.

setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'

    REPO_ROOT="$(cd "$(dirname "$BATS_TEST_FILENAME")/../.." && pwd)"

    WORK_DIR="$(mktemp -d)"
    mkdir -p "$WORK_DIR/res" "$WORK_DIR/src/N98/Util/Compatibility/Dataset" "$WORK_DIR/servedir"

    cp "$REPO_ROOT/build.sh" "$WORK_DIR/build.sh"
    cp "$REPO_ROOT/res/db-compatibility.json" "$WORK_DIR/res/db-compatibility.json"
    cp "$REPO_ROOT/src/N98/Util/Compatibility/Dataset/DatasetSchemaValidator.php" \
        "$WORK_DIR/src/N98/Util/Compatibility/Dataset/DatasetSchemaValidator.php"
    cp "$REPO_ROOT/src/N98/Util/Compatibility/Dataset/DatasetValidationException.php" \
        "$WORK_DIR/src/N98/Util/Compatibility/Dataset/DatasetValidationException.php"

    cat > "$WORK_DIR/servedir/valid.json" <<'JSON'
{
  "schemaVersion": "1.0.0",
  "datasetRevision": "remote-test",
  "publishedAt": "2026-02-01",
  "sources": [],
  "applications": [],
  "rules": [],
  "migrations": []
}
JSON
    echo 'this is not valid json' > "$WORK_DIR/servedir/invalid.json"

    PORT=$((20000 + RANDOM % 10000))
    (cd "$WORK_DIR/servedir" && php -S "127.0.0.1:${PORT}" >/dev/null 2>&1 &)
    SERVER_PID=""
    for _ in $(seq 1 30); do
        if (echo > "/dev/tcp/127.0.0.1/${PORT}") >/dev/null 2>&1; then
            break
        fi
        sleep 0.1
    done

    cd "$WORK_DIR"
}

teardown() {
    pkill -f "php -S 127.0.0.1:${PORT}" >/dev/null 2>&1 || true
    rm -rf "$WORK_DIR"
}

@test "build.sh: --skip-dataset-fetch validates and packages the existing snapshot with no network request" {
    run bash -c 'source ./build.sh; parse_args --skip-dataset-fetch; refresh_dataset; echo "REVISION=$DATASET_REVISION FALLBACK=$DATASET_FALLBACK"'

    assert_success
    assert_output --partial "existing snapshot is valid"
    assert_output --partial "--skip-dataset-fetch given"
    assert_output --partial "FALLBACK=false"
    refute_output --partial "fetching latest dataset"
}

@test "build.sh: with no dataset URL configured, packages the repository-maintained snapshot" {
    run bash -c 'source ./build.sh; parse_args; refresh_dataset; echo "REVISION=$DATASET_REVISION FALLBACK=$DATASET_FALLBACK"'

    assert_success
    assert_output --partial "no external dataset source configured"
    assert_output --partial "FALLBACK=false"
}

@test "build.sh: fetches and atomically replaces the dataset when the URL is reachable and valid" {
    run bash -c "source ./build.sh; parse_args; MAGERUN_DB_COMPATIBILITY_DATASET_URL='http://127.0.0.1:${PORT}/valid.json' refresh_dataset; echo \"REVISION=\$DATASET_REVISION FALLBACK=\$DATASET_FALLBACK\""

    assert_success
    assert_output --partial "REVISION=remote-test FALLBACK=false"

    run php -r 'echo json_decode(file_get_contents("res/db-compatibility.json"), true)["datasetRevision"];'
    assert_output "remote-test"
}

@test "build.sh: falls back to the bundled snapshot and warns when the fetched dataset is invalid" {
    run bash -c "source ./build.sh; parse_args; MAGERUN_DB_COMPATIBILITY_DATASET_URL='http://127.0.0.1:${PORT}/invalid.json' refresh_dataset; echo \"REVISION=\$DATASET_REVISION FALLBACK=\$DATASET_FALLBACK\""

    assert_success
    assert_output --partial "WARNING: fetched dataset failed schema validation"
    assert_output --partial "FALLBACK=true"

    run php -r 'echo json_decode(file_get_contents("res/db-compatibility.json"), true)["datasetRevision"];'
    refute_output "remote-test"
}

@test "build.sh: falls back to the bundled snapshot and warns when the dataset URL is unreachable" {
    run bash -c "source ./build.sh; parse_args; MAGERUN_DB_COMPATIBILITY_DATASET_URL='http://127.0.0.1:1/dataset.json' refresh_dataset; echo \"REVISION=\$DATASET_REVISION FALLBACK=\$DATASET_FALLBACK\""

    assert_success
    assert_output --partial "WARNING: could not fetch dataset"
    assert_output --partial "FALLBACK=true"
}

@test "build.sh: fails the build when no valid dataset is available at all" {
    echo 'not json' > res/db-compatibility.json

    run bash -c 'source ./build.sh; parse_args --skip-dataset-fetch; refresh_dataset'

    assert_failure
    assert_output --partial "No valid dataset available"
}
