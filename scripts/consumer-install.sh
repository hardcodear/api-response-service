#!/usr/bin/env bash
set -euo pipefail

repo_dir="$(pwd)"
workspace="$(mktemp -d)"
package_dir="$workspace/package"
app_dir="$workspace/app"
mkdir -p "$package_dir"

# Test the distributed tree, including export-ignore, rather than the checkout.
git archive --format=tar HEAD | tar -xf - -C "$package_dir"
test -f "$package_dir/README.md"
test -f "$package_dir/LICENSE.md"
test -f "$package_dir/config/apiresponse.php"
test ! -d "$package_dir/tests"
test ! -d "$package_dir/.github"
test ! -d "$package_dir/scripts"
test ! -d "$package_dir/vendor"

composer create-project laravel/laravel "$app_dir" "^${LARAVEL_VERSION}.0" \
  --no-install --no-scripts --prefer-dist --no-interaction --no-progress
cd "$app_dir"

# Assign a test-only version to the exported package. No release tag is created.
composer config repositories.apiresponse \
  "{\"type\":\"path\",\"url\":\"$package_dir\",\"options\":{\"symlink\":false,\"versions\":{\"hardcodear/api-response-service\":\"2.0.0\"}}}"
composer require 'hardcodear/api-response-service:^2.0' \
  --update-no-dev --prefer-dist --no-interaction --no-progress
cp .env.example .env
php artisan key:generate --ansi
php artisan vendor:publish --tag=apiresponse-config --ansi
test -f config/apiresponse.php
php artisan config:cache --ansi
php "$repo_dir/scripts/verify-consumer.php" "$app_dir"
php artisan config:clear --ansi
