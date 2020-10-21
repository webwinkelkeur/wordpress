<?php


namespace Valued\WordPress;


class GtinHandler {
	private $supported_plugings = [
		'product_feed'                    => 'handleProductFeed()',
		'webwinkelkeur/webwinkelkeur.php' => 'handleWWK'
	];
	private $product;

	public function __construct($product) {
		$this->product = $product;
	}

	private function getActivePluging() {
		$apl = get_option('active_plugins');
		$plugins = get_plugins();
		foreach ($apl as $p) {
			if (isset($plugins[$p]) && isset($this->supported_plugings[$p])) {
				return $this->supported_plugings[$p];
			}
		}
	}

	public function getGTIN() {
		$active_plugin = $this->getActivePluging();
		if ($active_plugin) {
			return call_user_func([$this, $active_plugin]);
		}
		return  get_post_meta($this->product->get_id(), 'wwk_gtin_id');
	}

	private function handleProductFeed(): int {
		return 123;
	}

	private function handleWWK(): int {
//		return woocommerce_gpf_show_element( 'gtin', $this->product);
		return 120;
	}
}
