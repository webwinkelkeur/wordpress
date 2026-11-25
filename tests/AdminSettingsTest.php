<?php

namespace WebwinkelKeur\WordPress\Tests;

use Valued\WordPress\Admin;

class AdminSettingsTest extends TestCase {
    public function set_up() {
        parent::set_up();
        self::createAdmin();
    }

    public function tear_down() {
        $_POST = $_REQUEST = [];
        parent::tear_down();
    }

    /**
     * Submit the settings form like WordPress would: $_POST and $_REQUEST
     * are slashed by wp_magic_quotes().
     */
    private function submitSettings(array $fields): string {
        $plugin = self::plugin();
        $post = [];
        foreach ($fields as $name => $value) {
            $post[$plugin->getOptionName($name)] = $value;
        }
        $_POST = wp_slash($post);
        $_REQUEST = wp_slash($post + [
            '_wpnonce' => wp_create_nonce($plugin->getOptionName('options_nonce')),
        ]);
        ob_start();
        try {
            (new Admin($plugin))->options_page();
        } finally {
            $output = ob_get_clean();
        }
        return $output;
    }

    private function option(string $name) {
        return get_option(self::plugin()->getOptionName($name));
    }

    public function test_values_are_saved_unslashed(): void {
        $gtin_key = "meta_keygtin'code\\x";
        $this->submitSettings([
            'wwk_shop_id' => '1234',
            'wwk_api_key' => 'abc.123',
            'custom_gtin' => $gtin_key,
            'invite' => '1',
            'invite_delay' => '3',
            'order_statuses' => ['wc-completed', 'wc-processing'],
        ]);
        $this->assertSame('1234', $this->option('wwk_shop_id'));
        $this->assertSame('abc.123', $this->option('wwk_api_key'));
        $this->assertSame($gtin_key, $this->option('custom_gtin'));
        $this->assertSame(['wc-completed', 'wc-processing'], $this->option('order_statuses'));
    }

    public function test_saving_twice_does_not_add_slashes(): void {
        $gtin_key = "custom_attributeMerk's";
        $this->submitSettings(['wwk_shop_id' => '1234', 'custom_gtin' => $gtin_key]);
        $this->submitSettings(['wwk_shop_id' => '1234', 'custom_gtin' => $this->option('custom_gtin')]);
        $this->assertSame($gtin_key, $this->option('custom_gtin'));
    }

    public function test_invalid_shop_id_is_rejected(): void {
        $output = $this->submitSettings(['wwk_shop_id' => '12ab']);
        $this->assertStringContainsString('Your shop ID can only contain digits.', $output);
        $this->assertFalse($this->option('wwk_shop_id'));
    }
}
