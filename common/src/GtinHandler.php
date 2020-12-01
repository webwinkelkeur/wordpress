<?php

namespace Valued\WordPress;

class GtinHandler {
    const SUPPORTED_PLUGINS = [
        'woosa-vandermeer/woosa-vandermeer.php' => ['vdm_ean'],
        'woocommerce-product-feeds/woocommerce-gpf.php' => null,
        'customer-reviews-woocommerce/ivole.php' => ['_cr_gtin'],
        'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => [
            '_wpm_gtin_code', '_wpm_ean_code'
        ],
        'woo-product-feed-pro/woocommerce-sea.php' => ['_woosea_gtin', '_woosea_ean'],
    ];
    private $product;
    private $gtin_meta_key;

    public function __construct(string $gtin_meta_key) {
        $this->gtin_meta_key = $gtin_meta_key;
    }

    public function setProduct(\WC_Product $product) {
        $this->product = $product;
    }

    public static function hasActivePlugin() {
        foreach (self::SUPPORTED_PLUGINS as $plugin_name => $key) {
            if (is_plugin_active($plugin_name)) {
                return $plugin_name;
            }
        }
        return null;
    }

    public function getGtin(string $custom_gtin_key = null) {
        if (!empty($custom_gtin_key)) {
            return $this->getGtinFromMeta($custom_gtin_key);
        }
        if (is_plugin_active('woocommerce-product-feeds/woocommerce-gpf.php')) {
            return $this->handleGpf();
        }
        foreach (self::SUPPORTED_PLUGINS as $plugin_name => $keys) {
            if ($keys && is_plugin_active($plugin_name)) {
                return $this->getFromPluginMeta($keys);
            }
        }
        return $this->getGtinFromMeta($this->gtin_meta_key);
    }

    private function getFromPluginMeta(array $keys) {
        foreach ($keys as $key) {
            if ($result = $this->getGtinFromMeta($key)) {
                return $result;
            }
        }
        return null;
    }

    private function getGtinFromMeta(string $key) {
        return (string) get_post_meta($this->product->get_id(), $key, true) ?: null;
    }

    private function handleGpf() {
        return (string) woocommerce_gpf_show_element('gtin', $this->product->post) ?: null;
    }
}
