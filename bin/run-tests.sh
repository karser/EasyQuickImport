#!/usr/bin/env bash

set -e

if [[ ! -e ./public/build/manifest.json ]]; then
    mkdir -p ./public/build
    echo "{}" > ./public/build/manifest.json
fi

composer install --dev --prefer-dist --no-interaction --no-scripts --no-progress --no-suggest

echo "Waiting for db to be ready..."
until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done
bin/console doctrine:migrations:migrate --no-interaction

vendor/bin/simple-phpunit -c ./phpunit.xml.dist
