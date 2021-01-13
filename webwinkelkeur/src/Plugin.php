<?php
namespace WebwinkelKeur\WordPress;

use Valued\WordPress\BasePlugin;

class Plugin extends BasePlugin {
    public function getSlug() {
        return 'webwinkelkeur';
    }

    public function getName() {
        return 'WebwinkelKeur';
    }

    public function getMainDomain() {
        return 'www.webwinkelkeur.nl';
    }

    public function getDashboardDomain() {
        return 'dashboard.webwinkelkeur.nl';
    }

    protected function getUpdateNotices(): array {
        return [
            '3.3' => sprintf(
                '<p>%s <a target="_blank" href="%s">%s</a></p>',
                __('New: WebwinkelKeur Product Reviews for WooCommerce', 'webwinkelkeur'),
                'https://www.webwinkelkeur.nl/product-reviews-nu-beschikbaar-voor-jouw-woocommerce-webshop/?utm_source=wordpress&utm_medium=notification&utm_campaign=update_product_reviews_available',
                __('Read more', 'webwinkelkeur')
            ),
        ];
    }
}
