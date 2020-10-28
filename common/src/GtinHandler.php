<?php

namespace Valued\WordPress;

class GtinHandler {
    const SUPPORTED_PLUGINS = [
        'woocommerce-product-feeds/woocommerce-gpf.php' => null,
        'customer-reviews-woocommerce/ivole.php' => '_cr_gtin',
        'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => '_wpm_gtin_code',
        'woo-product-feed-pro/woocommerce-sea.php' => '_woosea_gtin',
    ];
    private $product;
    private $plugin;

    public function __construct(BasePlugin $plugin) {

        $this->plugin = $plugin;
    }

    public function setProduct(\WC_Product $product): void {
        $this->product = $product;
    }

    public function hasActivePlugin(): bool {
        foreach (self::SUPPORTED_PLUGINS as $plugin_name => $key) {
            if (is_plugin_active($plugin_name)) {
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
        foreach (array_filter(self::SUPPORTED_PLUGINS, 'strlen') as $plugin_name => $key) {
            if (is_plugin_active($plugin_name)) {
                return $key;
            }
        }
        return $this->plugin->getGtinMetaKey();
    }

    private function getGtinFromMeta(string $key): ?string {
        return (string) get_post_meta($this->product->get_id(), $key, true) ?: null;
    }

    private function handleGpf(): ?string {
        return (string) woocommerce_gpf_show_element('gtin', $this->product->post) ?: null;
    }
}
