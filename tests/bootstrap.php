<?php
// Loaded by tests/run.sh through `wp eval-file`, inside a WordPress install
// with WooCommerce and the WebwinkelKeur plugin active.

require __DIR__ . '/lib.php';

$filter = getenv('TEST_FILTER') ?: '';
foreach (glob(__DIR__ . '/*Test.php') as $file) {
    if ($filter === '' || strpos(basename($file), $filter) !== false) {
        require $file;
    }
}

exit(Tests::run() ? 0 : 1);
