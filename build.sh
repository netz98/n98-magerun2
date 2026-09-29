#!/bin/bash
#
# build from clean checkout
#
# usage: ./build.sh from project root
set -euo pipefail

IFS=$'\n\t'
PHP_BIN="php"
BOX_BIN="./box.phar"
PHAR_OUTPUT_FILE="./n98-magerun2.phar"
COMPOSER_BIN="composer"

DATASET_FILE="./res/db-compatibility.json"
DATASET_SCHEMA_VALIDATOR="./src/N98/Util/Compatibility/Dataset/DatasetSchemaValidator.php"
DATASET_VALIDATION_EXCEPTION="./src/N98/Util/Compatibility/Dataset/DatasetValidationException.php"
SKIP_DATASET_FETCH=false
DATASET_REVISION=""
DATASET_FALLBACK="false"

function parse_args() {
  for arg in "$@"; do
    case "$arg" in
      --skip-dataset-fetch)
        SKIP_DATASET_FETCH=true
        ;;
    esac
  done
}

function system_setup() {
  if [ "$(uname -s)" != "Darwin" ]; then
    ulimit -Sn $(ulimit -Hn)
  fi
}

function check_dependencies() {
  DEPENDENCY_ERROR=false

  if command -v curl &>/dev/null; then
    echo "curl found"
  else
    echo "curl not found!"
    DEPENDENCY_ERROR=true
  fi

  if command -v git &>/dev/null; then
    echo "git found"
  else
    echo "git not found!"
    DEPENDENCY_ERROR=true
  fi

  if command -v $PHP_BIN &>/dev/null; then
    echo "php found"
  else
    echo "php not found!"
    DEPENDENCY_ERROR=true
  fi

  if [ $DEPENDENCY_ERROR = true ]; then
    echo "Some dependecies are not found. Cannot build."
    exit 1
  fi

}

function download_box() {
  if [ ! -f box.phar ]; then
    curl -L https://github.com/box-project/box/releases/download/4.7.0/box.phar -o $BOX_BIN
    chmod +x ./box.phar
  fi
}

function find_commit_timestamp() {
  LAST_COMMIT_TIMESTAMP="$(git log --format=format:%ct HEAD -1)" # reproducible build
}

# Validates a db-compatibility dataset file against its schema, using the plain PHP
# validator class directly (no composer autoload dependency, so this works even before
# `composer install` has run). Prints the dataset revision to stdout and exits 0 on success;
# prints validation errors to stderr and exits 1 on failure.
function validate_dataset_file() {
  local file="$1"

  $PHP_BIN -r '
    require $argv[1];
    require $argv[2];

    $path = $argv[3];
    $data = json_decode((string) @file_get_contents($path), true);
    if (!is_array($data)) {
        fwrite(STDERR, "not valid JSON: $path\n");
        exit(1);
    }

    $validator = new N98\Util\Compatibility\Dataset\DatasetSchemaValidator();
    $errors = $validator->validate($data);
    if (!empty($errors)) {
        fwrite(STDERR, implode("\n", $errors) . "\n");
        exit(1);
    }

    echo $data["datasetRevision"] . "\n";
  ' "$DATASET_VALIDATION_EXCEPTION" "$DATASET_SCHEMA_VALIDATOR" "$file"
}

# Refreshes the bundled db-compatibility dataset before packaging a release.
#
# Always validates the existing snapshot first. Unless --skip-dataset-fetch was passed and a
# dataset URL is configured via MAGERUN_DB_COMPATIBILITY_DATASET_URL, attempts to fetch the
# latest dataset, validates it, and only then atomically replaces the bundled file - on any
# fetch or validation failure the existing (already-validated) snapshot is kept and a visible
# warning is printed; the build only fails if no valid dataset is available at all.
function refresh_dataset() {
  echo "dataset: validating existing snapshot at ${DATASET_FILE}..."

  local validation_log
  validation_log="$(mktemp)"
  local existing_revision=""
  if existing_revision=$(validate_dataset_file "$DATASET_FILE" 2>"$validation_log"); then
    echo "dataset: existing snapshot is valid (revision ${existing_revision})."
  else
    echo "WARNING: existing dataset snapshot failed schema validation:"
    cat "$validation_log"
  fi
  rm -f "$validation_log"

  if [ "$SKIP_DATASET_FETCH" = true ]; then
    if [ -z "$existing_revision" ]; then
      echo "Fatal: --skip-dataset-fetch was given and the bundled dataset is invalid. No valid dataset available."
      exit 1
    fi
    echo "dataset: --skip-dataset-fetch given; packaging the existing snapshot without any network request."
    DATASET_REVISION="$existing_revision"
    DATASET_FALLBACK="false"
    return
  fi

  local dataset_url="${MAGERUN_DB_COMPATIBILITY_DATASET_URL:-}"

  if [ -z "$dataset_url" ]; then
    if [ -z "$existing_revision" ]; then
      echo "Fatal: no dataset URL is configured (MAGERUN_DB_COMPATIBILITY_DATASET_URL) and the bundled dataset is invalid."
      exit 1
    fi
    echo "dataset: no external dataset source configured; packaging the repository-maintained snapshot (revision ${existing_revision})."
    DATASET_REVISION="$existing_revision"
    DATASET_FALLBACK="false"
    return
  fi

  echo "dataset: fetching latest dataset from ${dataset_url}..."

  # Create the temp file in the same directory as DATASET_FILE so the later `mv` is an atomic
  # rename rather than a cross-filesystem copy.
  local tmp_file
  tmp_file="$(mktemp "$(dirname "$DATASET_FILE")/db-compatibility.json.XXXXXX")"

  if curl -fsSL --max-time 30 "$dataset_url" -o "$tmp_file"; then
    local fetched_revision
    validation_log="$(mktemp)"
    if fetched_revision=$(validate_dataset_file "$tmp_file" 2>"$validation_log"); then
      mv "$tmp_file" "$DATASET_FILE"
      echo "dataset: fetched and validated dataset revision ${fetched_revision}; replaced ${DATASET_FILE}."
      rm -f "$validation_log"
      DATASET_REVISION="$fetched_revision"
      DATASET_FALLBACK="false"
      return
    fi

    echo "WARNING: fetched dataset failed schema validation, keeping the existing snapshot:"
    cat "$validation_log"
    rm -f "$validation_log"
  else
    echo "WARNING: could not fetch dataset from ${dataset_url}, keeping the existing snapshot."
  fi

  rm -f "$tmp_file"

  if [ -z "$existing_revision" ]; then
    echo "Fatal: dataset fetch failed and the bundled snapshot is invalid. No valid dataset available."
    exit 1
  fi

  DATASET_REVISION="$existing_revision"
  DATASET_FALLBACK="true"
}

function create_new_phar() {
  # set composer suffix, otherwise Composer will generate a file with a unique identifier
  # which will then create a no reproducable phar file with a differenz MD5
  $COMPOSER_BIN config autoloader-suffix N98MagerunNTS

  # Run install again to get the latest install.php and install.json file
  $COMPOSER_BIN install

  $PHP_BIN $BOX_BIN compile

  # unset composer suffix
  $COMPOSER_BIN config autoloader-suffix --unset

  # Set timestamp of newly generted phar file to the commit timestamp
  $PHP_BIN -f build/phar/phar-timestamp.php -- $LAST_COMMIT_TIMESTAMP

  # Run a signature verification after the timestamp manipulation
  $PHP_BIN $BOX_BIN verify $PHAR_OUTPUT_FILE

  # make phar executable
  chmod +x $PHAR_OUTPUT_FILE

  # Print version of new phar file which is also a test
  $PHP_BIN -f $PHAR_OUTPUT_FILE -- --version

  # List new phar file for debugging
  ls -al "$PHAR_OUTPUT_FILE"
}

function print_info_before_build() {
  echo "with: $($PHP_BIN --version | head -n 1)"
  echo "with: $("${COMPOSER_BIN}" --version)"
  echo "with: $("${BOX_BIN}" --version)"
  echo "build version: $(git --no-pager log --oneline -1)"
  echo "last commit timestamp: ${LAST_COMMIT_TIMESTAMP}"
  echo "provision: ulimits (soft) set from $(ulimit -Sn) to $(ulimit -Hn) (hard) for faster phar builds..."
  echo "db-compatibility dataset: revision ${DATASET_REVISION} (fallback to bundled snapshot: ${DATASET_FALLBACK})"
}

# Guarded so tests can `source` this file to exercise individual functions (e.g. the dataset
# refresh logic) without triggering a full build.
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
  parse_args "$@"
  check_dependencies
  system_setup
  download_box
  find_commit_timestamp
  refresh_dataset
  print_info_before_build
  create_new_phar

  echo "done."
fi
