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
}
