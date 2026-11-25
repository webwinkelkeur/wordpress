<?php

/**
 * Submit the settings form like WordPress would: $_POST and $_REQUEST are
 * slashed by wp_magic_quotes().
 */
function submit_settings(array $fields): string {
    $plugin = plugin();
    $post = [];
    foreach ($fields as $name => $value) {
        $post[$plugin->getOptionName($name)] = $value;
    }
    $_POST = wp_slash($post);
    $_REQUEST = wp_slash($post + [
        '_wpnonce' => wp_create_nonce($plugin->getOptionName('options_nonce')),
    ]);
    try {
        ob_start();
        (new \Valued\WordPress\Admin($plugin))->options_page();
        return ob_get_clean();
    } finally {
        $_POST = $_REQUEST = [];
    }
}

test('settings: values with quotes and backslashes are saved unslashed', function () {
    wp_set_current_user(1);
    $gtin_key = "meta_keygtin'code\\x";
    submit_settings([
        'wwk_shop_id' => '1234',
        'wwk_api_key' => 'abc.123',
        'custom_gtin' => $gtin_key,
        'invite' => '1',
        'invite_delay' => '3',
        'order_statuses' => ['wc-completed', 'wc-processing'],
    ]);
    assert_same('1234', get_option(plugin()->getOptionName('wwk_shop_id')));
    assert_same('abc.123', get_option(plugin()->getOptionName('wwk_api_key')));
    assert_same($gtin_key, get_option(plugin()->getOptionName('custom_gtin')));
    assert_same(['wc-completed', 'wc-processing'], get_option(plugin()->getOptionName('order_statuses')));
});

test('settings: saving twice does not add slashes', function () {
    wp_set_current_user(1);
    $gtin_key = "custom_attributeMerk's";
    foreach ([1, 2] as $_) {
        submit_settings(['wwk_shop_id' => '1234', 'custom_gtin' => $gtin_key]);
    }
    assert_same($gtin_key, get_option(plugin()->getOptionName('custom_gtin')));
});
