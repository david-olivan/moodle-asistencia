#!/usr/bin/env bash
# Installs the shared git hooks defined in .githooks/ into .git/hooks/.
# Run once after cloning: bash scripts/install-hooks.sh

set -euo pipefail

REPO_ROOT=$(git rev-parse --show-toplevel)
HOOKS_SRC="$REPO_ROOT/.githooks"
HOOKS_DST="$REPO_ROOT/.git/hooks"

for hook in "$HOOKS_SRC"/*; do
    name=$(basename "$hook")
    dst="$HOOKS_DST/$name"

    if [ -L "$dst" ] || [ -f "$dst" ]; then
        echo "Replacing existing hook: $name"
        rm "$dst"
    fi

    ln -s "$hook" "$dst"
    chmod +x "$hook"
    echo "Installed: $name -> $dst"
done

echo ""
echo "All hooks installed. Make sure phpcs is available:"
echo "  composer install"
