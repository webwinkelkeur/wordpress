<?php


namespace Valued\WordPress;


class GtinHandler {
	private $supported_plugings = [
		'woocommerce-product-feeds/woocommerce-gpf.php' => 'handleGPF',
		'customer-reviews-woocommerce/ivole.php' => 'handleCR',
		'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => 'handlePgtin'
	];
	private $product;

	public function __construct($product = null) {
		$this->product = $product;
	}

	public function getActivePlugin(): ?string {
		$apl = get_option('active_plugins');
		$plugins = get_plugins();
		foreach ($apl as $p) {
			if (isset($plugins[$p]) && isset($this->supported_plugings[$p])) {
				return $this->supported_plugings[$p];
			}
		}
		return null;
	}

	public function getGTIN(): ?string {
		$active_plugin = $this->getActivePlugin();
		if ($active_plugin) {
			return call_user_func([$this, $active_plugin]);
		}
		return get_post_meta($this->product->get_id(), '_product_gtin_code')[0] ?? null;
	}

	private function handleGPF(): ?string {
		return woocommerce_gpf_show_element('gtin', $this->product->post)[0] ?? null;
	}

	private function handleCR(): ?string {
		return get_post_meta($this->product->get_id(), '_cr_gtin')[0] ?? null;
	}

	private function handlePgtin(): ?string {
		return get_post_meta($this->product->get_id(), '_wpm_gtin_code')[0] ?? null;
	}
}
