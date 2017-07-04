#!/bin/bash

# Installs new icons to src/AppBundle/Resources/views/icons.
# These icons should be committed to the git repository. Old icons are not
# removed.

set -e

PROJECT_ROOT=$(dirname $(dirname $(realpath $0)))
PATH="$PROJECT_ROOT/node_modules/.bin:$PATH"
TEMP=$(mktemp -d)
OUT="$PROJECT_ROOT/src/AppBundle/Resources/views/icons"

trap 'rm -rf "$TEMP"' EXIT

fontello-cli install --config "$PROJECT_ROOT/fontello.json" --font "$TEMP" --css "$TEMP"
font-blast "$TEMP/raddit.svg" "$TEMP"
mv "$TEMP/svg/"*.svg "$OUT"
