<?php

namespace Valued\WordPress;

class GtinHandler {
    const SUPPORTED_PLUGINS = [
        'woosa-vandermeer/woosa-vandermeer.php' => ['vdm_ean'],
        'wpseo-woocommerce/wpseo-woocommerce.php' => [
            'gtin13',
            'gtin8',
            'gtin12',
            'gtin14',
            'isbn',
        ],
        'woocommerce-product-feeds/woocommerce-gpf.php' => null,
        'customer-reviews-woocommerce/ivole.php' => ['_cr_gtin'],
        'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => [
            '_wpm_gtin_code', '_wpm_ean_code'
        ],
        'woo-product-feed-pro/woocommerce-sea.php' => ['_woosea_gtin', '_woosea_ean'],
    ];
    const ATTRIBUTE_PREFIX = 'custom_attribute';
    const META_PREFIX = 'meta_key';

    private $product;
    private $gtin_meta_key;

    public function __construct(string $gtin_meta_key) {
        $this->gtin_meta_key = $gtin_meta_key;
    }

    public function setProduct(\WC_Product $product) {
        $this->product = $product;
    }

    public static function getActivePlugin() {
        foreach (self::SUPPORTED_PLUGINS as $plugin_name => $key) {
            if (is_plugin_active($plugin_name)) {
                return $plugin_name;
            }
        }
        return null;
    }

    public function getGtin(string $custom_gtin_key = null) {
        if (!empty($custom_gtin_key)) {
            return $this->getGtinFromKey($custom_gtin_key);
        }
        if (function_exists('woocommerce_gpf_show_element')) {
            return (string) woocommerce_gpf_show_element('gtin', $this->product->post) ?: null;
        }
        if (!is_plugin_active('wpseo-woocommerce/wpseo-woocommerce.php')) {
            return $this->handleWpseoPlugin();
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

    private function getGtinFromKey(string $custom_gtin_key) {
        if (strpos($custom_gtin_key, self::ATTRIBUTE_PREFIX) === 0) {
            return $this->product->get_attribute(
                substr($custom_gtin_key, strlen(self::ATTRIBUTE_PREFIX))
            );
        }
        if (strpos($custom_gtin_key, self::META_PREFIX) === 0) {
            return $this->getGtinFromMeta(
                substr($custom_gtin_key, strlen(self::META_PREFIX))
            );
        }
        return null;
    }

    private function handleWpseoPlugin() {
        $keys = get_post_meta($this->product->get_id(), 'wpseo_global_identifier_values')[0] ?? null;
        foreach (self::SUPPORTED_PLUGINS['wpseo-woocommerce/wpseo-woocommerce.php'] as $gtin_key) {
            if (!empty($keys[$gtin_key])) {
                return (string) $keys[$gtin_key];
            }
        }
        return null;
    }
}
