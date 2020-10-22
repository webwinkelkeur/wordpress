<?php


namespace Valued\WordPress;


class GtinHandler {
	private $supported_plugings = [
		'woocommerce-product-feeds/woocommerce-gpf.php' => 'handleWWK'
	];
	private $product;

	public function __construct($product) {
		$this->product = $product;
	}

	private function getActivePlugin() {
		$apl = get_option('active_plugins');
		$plugins = get_plugins();
		foreach ($apl as $p) {
			if (isset($plugins[$p]) && isset($this->supported_plugings[$p])) {
				return $this->supported_plugings[$p];
			}
		}
	}

	public function getGTIN(): ?string {
		$active_plugin = $this->getActivePlugin();
		if ($active_plugin) {
			return call_user_func([$this, $active_plugin]);
		}
		$wwk_gtin = get_post_meta($this->product->get_id(), 'wwk_gtin');
		return $wwk_gtin ?: null;
	}

	private function handleWWK(): ?string {
		$gtin = woocommerce_gpf_show_element('gtin', $this->product->post);
		return $gtin ?: null;
	}
}
