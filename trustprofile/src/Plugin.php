<?php
namespace TrustProfile\WordPress;

use Valued\WordPress\BasePlugin;

class Plugin extends BasePlugin {
    public function getSlug() {
        return 'trustprofile';
    }

    public function getName() {
        return 'TrustProfile';
    }

    public function getMainDomain() {
        return 'www.trustprofile.io';
    }

    public function getDashboardDomain() {
        return 'dashboard.trustprofile.io';
    }
}
