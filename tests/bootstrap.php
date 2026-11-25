<?php
// PHPUnit bootstrap: load the WordPress test library with WooCommerce and
// the WebwinkelKeur plugin active. See tests/run.sh.

$tests_dir = getenv('WP_TESTS_DIR');
if (!$tests_dir || !is_file("{$tests_dir}/includes/functions.php")) {
    fwrite(STDERR, "Set WP_TESTS_DIR to the WordPress test library, or use tests/run.sh.\n");
    exit(1);
}

define('WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php');

require "{$tests_dir}/includes/functions.php";

tests_add_filter('muplugins_loaded', function () {
    require WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
    require WP_PLUGIN_DIR . '/webwinkelkeur/webwinkelkeur.php';
});

tests_add_filter('setup_theme', function () {
    WC_Install::install();
    // Reload roles, so the capabilities added by WooCommerce are known.
    $GLOBALS['wp_roles'] = null;
    wp_roles();
    \WebwinkelKeur\WordPress\Plugin::getInstance()->activatePlugin();
});

require "{$tests_dir}/includes/bootstrap.php";
require __DIR__ . '/includes/TestCase.php';
