<?php
// Configuration for the WordPress test library, used by tests/run.sh.

define('ABSPATH', rtrim(getenv('WP_CORE_DIR'), '/') . '/');

define('DB_NAME', getenv('DB_NAME') ?: 'wp_tests');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');
define('WP_PHP_BINARY', 'php');
define('WPLANG', '');
define('WP_DEBUG', true);

// Keep WooCommerce from calling home during tests.
define('WP_HTTP_BLOCK_EXTERNAL', true);
