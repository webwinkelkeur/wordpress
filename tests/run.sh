#!/usr/bin/env bash
# Run the integration tests against a throwaway WordPress + WooCommerce
# install in Docker. Usage: tests/run.sh [test-file-filter]
#
# WP_VERSION and WC_VERSION select the versions (default: latest).
set -euo pipefail

cd "$(dirname "$0")/.."
id="wwk-test-$$"
cli_image="${WP_CLI_IMAGE:-wordpress:cli}"
db_image="${DB_IMAGE:-mariadb:10.6}"

cleanup() {
    docker rm -f "$id-db" >/dev/null 2>&1 || true
    docker volume rm "$id-www" >/dev/null 2>&1 || true
    docker network rm "$id" >/dev/null 2>&1 || true
}
trap cleanup EXIT

docker network create "$id" >/dev/null
docker volume create "$id-www" >/dev/null
docker volume create wwk-test-cache >/dev/null
docker run -d --rm --name "$id-db" --network "$id" \
    -e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1 -e MARIADB_DATABASE=wp \
    "$db_image" >/dev/null

docker run --rm --network "$id" \
    -v "$id-www:/var/www/html" \
    -v wwk-test-cache:/tmp/.wp-cli/cache \
    -v "$PWD:/src:ro" \
    --user 33:33 -e HOME=/tmp \
    -e DB_HOST="$id-db" \
    -e WP_VERSION="${WP_VERSION:-latest}" \
    -e WC_VERSION="${WC_VERSION:-}" \
    -e TEST_FILTER="${1:-}" \
    "$cli_image" sh -euc '
wp() { php -d memory_limit=1G /usr/local/bin/wp --quiet "$@"; }
cd /var/www/html
for i in $(seq 30); do
    php -r "mysqli_report(MYSQLI_REPORT_OFF); exit(@mysqli_connect(getenv(\"DB_HOST\"), \"root\", \"\", \"wp\") ? 0 : 1);" && break
    sleep 1
done
wp core download --version="$WP_VERSION"
wp config create --dbname=wp --dbuser=root --dbpass= --dbhost="$DB_HOST"
wp core install --url=http://localhost --title=Test --admin_user=admin \
    --admin_password=admin --admin_email=admin@example.com --skip-email
wp plugin install woocommerce ${WC_VERSION:+--version="$WC_VERSION"} --activate
cp -rL /src/webwinkelkeur wp-content/plugins/webwinkelkeur
wp plugin activate webwinkelkeur
echo "WordPress $(wp core version), WooCommerce $(wp plugin get woocommerce --field=version), PHP $(php -r "echo PHP_VERSION;")"
php -d memory_limit=1G /usr/local/bin/wp eval-file /src/tests/bootstrap.php
'
