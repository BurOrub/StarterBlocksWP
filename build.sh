#!/bin/sh
# Compila src/index.jsx in build/index.js. Usa esbuild del tema (nessuna installazione): React/JSX diventano wp.element.createElement.
# Uso: sh build.sh [percorso-di-esbuild]
cd "$(dirname "$0")"
ESB="${1:-../../themes/starter-theme/node_modules/.bin/esbuild}"
"$ESB" src/index.jsx --bundle --format=iife --jsx=transform --jsx-factory=wp.element.createElement --jsx-fragment=wp.element.Fragment --target=es2019 --outfile=build/index.js --log-level=warning
