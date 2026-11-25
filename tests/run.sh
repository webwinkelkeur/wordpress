#!/usr/bin/env bash
# Run the PHPUnit tests against WordPress + WooCommerce in throwaway Docker
# containers. Arguments are passed to PHPUnit, e.g.:
#
#     tests/run.sh --filter ReviewSync
#
# PHP_VERSION, WP_VERSION and WC_VERSION select the versions
# (default: PHP 8.4, latest WordPress and WooCommerce).
set -euo pipefail

cd "$(dirname "$0")/.."
php_version="${PHP_VERSION:-8.4}"
id="wwk-test-$$"
image="wwk-test:php$php_version"

cleanup() {
    docker rm -f "$id-db" >/dev/null 2>&1 || true
    docker network rm "$id" >/dev/null 2>&1 || true
}
trap cleanup EXIT

docker build -q -t "$image" --build-arg PHP_VERSION="$php_version" tests >/dev/null
docker network create "$id" >/dev/null
docker volume create wwk-test-cache >/dev/null
docker run -d --rm --name "$id-db" --network "$id" \
    -e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1 -e MARIADB_DATABASE=wp_tests \
    "${DB_IMAGE:-mariadb:10.6}" >/dev/null

docker run --rm --network "$id" \
    -v wwk-test-cache:/cache \
    -v "$PWD:/src:ro" \
    -e HOME=/tmp \
    -e COMPOSER_CACHE_DIR=/cache/composer \
    -e WP_CLI_CACHE_DIR=/cache/wp-cli \
    -e COMPOSER_VENDOR_DIR=/tmp/vendor \
    -e DB_HOST="$id-db" \
    -e WP_VERSION="${WP_VERSION:-latest}" \
    -e WC_VERSION="${WC_VERSION:-latest-stable}" \
    -e WP_CORE_DIR=/tmp/wordpress \
    -e WP_TESTS_DIR=/tmp/wp-tests/vendor/wp-phpunit/wp-phpunit \
    "$image" sh -euc '
quiet() { out=$("$@" 2>&1) || { echo "$out"; return 1; }; }
wp() { php -d memory_limit=1G /usr/local/bin/wp --quiet "$@"; }

cd /src
quiet composer install --no-interaction --no-progress

wp core download --path="$WP_CORE_DIR" --version="$WP_VERSION"
wp_version=$(wp core version --path="$WP_CORE_DIR")

# The test library is versioned like WordPress, but not every patch release
# is published; use the latest one for this major.minor version.
mkdir -p /tmp/wp-tests
echo "{}" > /tmp/wp-tests/composer.json
COMPOSER_VENDOR_DIR=/tmp/wp-tests/vendor \
    quiet composer require --working-dir=/tmp/wp-tests --no-interaction --no-progress \
    "wp-phpunit/wp-phpunit:$(echo "$wp_version" | cut -d. -f1,2).*"

plugins="$WP_CORE_DIR/wp-content/plugins"
wc_zip="/cache/woocommerce.$WC_VERSION.zip"
if [ "$WC_VERSION" = latest-stable ] || [ ! -f "$wc_zip" ]; then
    curl -fsSL -o "$wc_zip~" "https://downloads.wordpress.org/plugin/woocommerce.$WC_VERSION.zip"
    mv "$wc_zip~" "$wc_zip"
fi
unzip -q "$wc_zip" -d "$plugins"
cp -rL /src/webwinkelkeur "$plugins/webwinkelkeur"

for i in $(seq 30); do
    php -r "mysqli_report(MYSQLI_REPORT_OFF); exit(@mysqli_connect(getenv(\"DB_HOST\"), \"root\", \"\") ? 0 : 1);" && break
    sleep 1
done

echo "PHP $(php -r "echo PHP_VERSION;"), WordPress $wp_version, WooCommerce $(sed -n "s/^ \* Version: //p" "$plugins/woocommerce/woocommerce.php")"
exec /tmp/vendor/bin/phpunit "$@"
' sh "$@"
