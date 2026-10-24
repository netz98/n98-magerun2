# PHP workflows use the project's DDEV environment and its configured Magento/Mage-OS.
# Documentation workflows use Node.js and npm on the host, as in the docs CI job.
set positional-arguments

# List all available recipes.
default:
    just --list

# Start DDEV (post-start hooks install dependencies and prepare the test environment).
[group('development')]
dev:
    ddev start

# Stop this project's DDEV environment.
[group('development')]
stop:
    ddev stop

# Open a shell in the DDEV web container.
[group('development')]
shell:
    ddev ssh

# Install PHP dependencies from composer.lock.
[group('development')]
install:
    ddev composer install

# Run the CLI from source; defaults to listing commands (e.g. just run sys:info).
[group('development')]
run *args:
    ddev mr2-dev "$@"

# Run PHPUnit against the configured Magento/Mage-OS; accepts PHPUnit arguments.
[group('testing')]
test *args:
    ddev unit-test "$@"

# Run the n98-magerun2 BATS suite; accepts BATS arguments.
[group('testing')]
test-bats *args:
    ddev bats-magerun "$@"

# Run the Magento/Mage-OS core BATS suite; accepts BATS arguments.
[group('testing')]
test-core *args:
    ddev bats-magento-core "$@"

# Run all PHP and BATS test suites.
[group('testing')]
test-all: test test-bats test-core

# Check PHP code style without changing files (the existing DDEV QA workflow).
[group('code quality')]
qa:
    ddev qa

# Apply PHP code-style fixes; accepts PHP-CS-Fixer arguments.
[group('code quality')]
format *args:
    ddev exec vendor/bin/php-cs-fixer fix "$@"

# Run PHPStan separately (currently disabled in the project's QA workflow).
[group('code quality')]
analyse *args:
    ddev exec vendor/bin/phpstan analyse "$@"

# Build the PHAR in DDEV; accepts build.sh arguments, e.g. --skip-dataset-fetch.
[group('build')]
build *args:
    ddev exec ./build.sh "$@"

# Install documentation dependencies from package-lock.json (requires host Node.js/npm).
[group('documentation')]
docs-install:
    npm --prefix docs ci

# Start the documentation development server; accepts Docusaurus arguments.
[group('documentation')]
docs-dev *args:
    npm --prefix docs run start -- "$@"

# Run the documentation configuration tests used by CI.
[group('documentation')]
docs-test:
    cd docs && node --test tests/*.test.cjs

# Lint the documentation site's JavaScript.
[group('documentation')]
docs-lint:
    npm --prefix docs run lint

# Build the static documentation site.
[group('documentation')]
docs-build:
    npm --prefix docs run build

# Show the working tree status and branch tracking information.
[group('git maintenance')]
git-status:
    git status --short --branch
    git branch -vv

# Fetch all remotes and prune stale remote-tracking branches (not local branches).
[group('git maintenance')]
git-fetch:
    git fetch --all --prune

# Preview local branches merged into base (default: develop); pass dry_run=false to delete.
[group('git maintenance')]
git-clean-merged base='develop' dry_run='true':
    #!/bin/sh
    set -eu
    base="$1"
    dry_run="$2"
    case "$dry_run" in
        true|false) ;;
        *) echo 'dry_run must be true or false' >&2; exit 1 ;;
    esac
    git rev-parse --verify "$base^{commit}" >/dev/null
    # Worktree occupancy is included so linked worktrees are protected too.
    branches=$(git for-each-ref --merged="$base" --format='%(if)%(worktreepath)%(then)occupied%(else)free%(end) %(refname:short)' refs/heads/)
    printf '%s\n' "$branches" | while read -r occupancy branch; do
        [ "$occupancy" = free ] || continue
        case "$branch" in
            develop|main|master|"$base") continue ;;
        esac
        if [ "$dry_run" = true ]; then
            printf 'Would delete: %s\n' "$branch"
        else
            git branch -d -- "$branch"
        fi
    done
