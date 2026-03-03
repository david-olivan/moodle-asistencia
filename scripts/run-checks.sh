#!/usr/bin/env bash
# Run the same checks as CI locally and write output to ci-last-run.log.
# Usage: bash scripts/run-checks.sh [--fix]
#
# --fix   run phpcbf to auto-fix phpcs violations after reporting them.

set -euo pipefail

LOG="ci-last-run.log"
FIX=false
if [[ "${1:-}" == "--fix" ]]; then
    FIX=true
fi

if [ ! -f "vendor/bin/phpcs" ]; then
    echo "ERROR: vendor/bin/phpcs not found. Run: composer install" >&2
    exit 1
fi

PHPCS="vendor/bin/phpcs"
PHPCBF="vendor/bin/phpcbf"
PASS=true

run_check() {
    local label="$1"
    shift
    echo "" | tee -a "$LOG"
    echo "=== $label ===" | tee -a "$LOG"
    if "$@" 2>&1 | tee -a "$LOG"; then
        echo "PASS: $label" | tee -a "$LOG"
    else
        echo "FAIL: $label" | tee -a "$LOG"
        PASS=false
    fi
}

# Truncate/create the log file.
: > "$LOG"
echo "ci-last-run — $(date -u '+%Y-%m-%dT%H:%M:%SZ')" | tee -a "$LOG"
echo "PHP: $(php --version | head -1)" | tee -a "$LOG"

run_check "PHP Lint" php -l $(find . \
    -name '*.php' \
    -not -path './vendor/*' \
    -not -path './ci/*' \
    -not -path './node_modules/*')

run_check "PHP Code Checker (moodle standard)" "$PHPCS" --standard=moodle \
    --extensions=php \
    --ignore=vendor,ci,node_modules \
    .

if $FIX; then
    echo "" | tee -a "$LOG"
    echo "=== phpcbf auto-fix ===" | tee -a "$LOG"
    "$PHPCBF" --standard=moodle \
        --extensions=php \
        --ignore=vendor,ci,node_modules \
        . 2>&1 | tee -a "$LOG" || true
fi

echo "" | tee -a "$LOG"
if $PASS; then
    echo "All checks passed. Full output saved to $LOG"
else
    echo "One or more checks FAILED. Full output saved to $LOG"
    exit 1
fi
