<?php

namespace WebwinkelKeur\WordPress\Tests;

use WP_Ajax_UnitTestCase;
use WPAjaxDieContinueException;

/** @group ajax */
class ProductKeysAjaxTest extends WP_Ajax_UnitTestCase {
    use Helpers;

    public function set_up() {
        parent::set_up();
        // _handleAjax() fires admin_init. The hook backup restored by the base
        // class brings back these update checks, which would call WordPress.org.
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
    }

    private function request(array $get): array {
        $wc = self::plugin()->woocommerce;
        // WordPress slashes $_GET in wp_magic_quotes().
        $_GET = wp_slash($get + ['_wpnonce' => wp_create_nonce($wc->getProductKeysNonce())]);
        try {
            $this->_handleAjax($wc->getProductKeysAction());
        } catch (WPAjaxDieContinueException $e) {
        }
        return json_decode($this->_last_response, true);
    }

    public function test_selected_key_with_quote_is_marked_selected(): void {
        $this->_setRole('administrator');
        $meta_key = "gtin'code";
        // add_post_meta() expects a slashed key.
        add_post_meta(self::createProduct(), wp_slash($meta_key), '8712345678906');

        $response = $this->request(['selected_key' => "meta_key{$meta_key}"]);

        $this->assertTrue($response['status']);
        $selected = array_filter($response['data'], function (array $key): bool {
            return $key['selected'];
        });
        $this->assertSame(["meta_key{$meta_key}"], array_column($selected, 'option_value'));
        $this->assertContains(
            ['option_value' => "meta_key{$meta_key}", 'label' => "{$meta_key} (e.g. \"8712345678906\")",
             'suggested' => true, 'selected' => true],
            $response['data'],
        );
    }

    public function test_non_admin_is_refused(): void {
        $this->_setRole('subscriber');
        $response = $this->request([]);
        $this->assertFalse($response['status']);
    }
}
