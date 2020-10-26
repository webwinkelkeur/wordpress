<?php


namespace Valued\WordPress;


class GtinHandler {
	private $supported_plugings = [
		'woocommerce-product-feeds/woocommerce-gpf.php' => [
			'callback' => 'handleGpf',
			'key' => 'gtin',
		],
		'customer-reviews-woocommerce/ivole.php' => [
			'callback' => 'getGtinFromMeta',
			'key' => '_cr_gtin',
		],
		'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => [
			'callback' => 'getGtinFromMeta',
			'key' => '_wpm_gtin_code',
		],
		'woo-product-feed-pro/woocommerce-sea.php' => [
			'callback' => 'getGtinFromMeta',
			'key' => '_woosea_gtin',
		],
	];
	private $product;

	public function __construct($product = null) {
		$this->product = $product;
	}

	public function getActivePlugin(): ?array {
		$plugins = get_plugins();
		foreach (get_option('active_plugins') as $p) {
			if (isset($plugins[$p]) && isset($this->supported_plugings[$p])) {
				return $this->supported_plugings[$p];
			}
		}
		return null;
	}

	public function getGtin(): ?string {
		$active_plugin = $this->getActivePlugin();
		if ($active_plugin) {
			return call_user_func([$this, $active_plugin['callback']], $active_plugin['key']);
		}
		return $this->getGtinFromMeta('_wwk_gtin_code');
	}

	private function getGtinFromMeta($key) {
		return get_post_meta($this->product->get_id(), $key)[0] ?? null;
	}

	private function handleGpf($key): ?string {
		return woocommerce_gpf_show_element($key, $this->product->post) ?: null;
	}
}
