<?php

namespace Valued\WordPress;

class GtinHandler {
    const SUPPORTED_PLUGINS = [
        'woocommerce-product-feeds/woocommerce-gpf.php',
        'customer-reviews-woocommerce/ivole.php',
        'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php',
        'woo-product-feed-pro/woocommerce-sea.php',
    ];
    const GTIN_META_KEYS = [
        'customer-reviews-woocommerce/ivole.php' => '_cr_gtin',
        'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => '_wpm_gtin_code',
        'woo-product-feed-pro/woocommerce-sea.php' => '_woosea_gtin',
    ];
    private $product;

    public function __construct(\WC_Product $product = null) {
        $this->product = $product;
    }

    public function hasActivePlugin(): bool {
        foreach (self::SUPPORTED_PLUGINS as $plugin) {
            if (is_plugin_active($plugin)) {
                return true;
            }
        }
        return false;
    }

    public function getGtin(): ?string {
        if (is_plugin_active('woocommerce-product-feeds/woocommerce-gpf.php')) {
            return $this->handleGpf();
        }
        return $this->getGtinFromMeta($this->getGtinMetaKey());
    }

    private function getGtinMetaKey(): string {
        foreach (self::GTIN_META_KEYS as $plugin => $key) {
            if (is_plugin_active($plugin)) {
                return $key;
            }
        }
        return '_wwk_gtin_code';
    }

    private function getGtinFromMeta(string $key): ?string {
        return (string) get_post_meta($this->product->get_id(), $key, true) ?: null;
    }

    private function handleGpf(): ?string {
        return woocommerce_gpf_show_element('gtin', $this->product->post) ?: null;
    }
}
