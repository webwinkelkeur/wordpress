<?php

class AjaxDie extends Exception {}

/** Call an AJAX handler and return its decoded JSON response. */
function call_ajax(callable $handler, array $get): array {
    add_filter('wp_doing_ajax', '__return_true');
    $die_handler = function () {
        return function () {
            throw new AjaxDie();
        };
    };
    add_filter('wp_die_ajax_handler', $die_handler);
    $_GET = wp_slash($get);
    $_REQUEST = $_GET;
    ob_start();
    try {
        $handler();
    } catch (AjaxDie $e) {
    } finally {
        $output = ob_get_clean();
        $_GET = $_REQUEST = [];
        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', $die_handler);
    }
    return json_decode($output, true);
}

test('product keys: selected key with a quote is marked selected', function () {
    wp_set_current_user(1);
    $meta_key = "gtin'code";
    // add_post_meta() expects a slashed key.
    add_post_meta(create_product(), wp_slash($meta_key), '8712345678906');

    $wc = plugin()->woocommerce;
    $response = call_ajax([$wc, 'getProductKeys'], [
        '_wpnonce' => wp_create_nonce($wc->getProductKeysNonce()),
        'selected_key' => "meta_key{$meta_key}",
    ]);

    assert_same(true, $response['status'] ?? null, 'Response: ' . var_export($response, true));
    $selected = array_values(array_filter($response['data'], function ($key) {
        return $key['selected'];
    }));
    assert_same(["meta_key{$meta_key}"], array_column($selected, 'option_value'));
});
