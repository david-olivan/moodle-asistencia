#!/usr/bin/env bash
# Run ESLint on the plugin's AMD JavaScript files using Moodle's ruleset.
# Usage: bash scripts/eslint.sh
#
# Requires: node + npm installed, and npm install already run in the Moodle root.

set -euo pipefail

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
MOODLE_DIR="$(cd "$PLUGIN_DIR/../../moodle" && pwd)"
AMD_SRC="$PLUGIN_DIR/amd/src"

if [ ! -f "$MOODLE_DIR/node_modules/.bin/eslint" ]; then
    echo "ERROR: node_modules not found in $MOODLE_DIR" >&2
    echo "       Run: cd $MOODLE_DIR && npm install" >&2
    exit 1
fi

echo "Running ESLint on $AMD_SRC ..."

cd "$MOODLE_DIR"
node_modules/.bin/eslint \
    --resolve-plugins-relative-to "$MOODLE_DIR" \
    --parser @babel/eslint-parser \
    --parser-options "sourceType:module,requireConfigFile:false" \
    -c "$MOODLE_DIR/.eslintrc" \
    "$AMD_SRC"

echo "ESLint passed."
