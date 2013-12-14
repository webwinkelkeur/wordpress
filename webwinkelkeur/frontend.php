<?php

class WebwinkelkeurFrontend {
    private $sidebar_printed = false;

    public function __construct() {
        foreach(array(
            'wp_head',
            'wp_meta',
            'wp_footer',
            'wp_print_scripts',
        ) as $action)
            add_action($action, array($this, 'sidebar'));
    }

    public function sidebar() {
        if($this->sidebar_printed) return;
        $this->sidebar_printed = true;

        if(!get_option('webwinkelkeur_sidebar')) {
            echo '<!-- Webwinkelkeur: sidebar niet geactiveerd -->';
            return;
        }

        $wwk_shop_id = (int) get_option('webwinkelkeur_wwk_shop_id');
        if(!$wwk_shop_id) {
            echo '<!-- Webwinkelkeur: webwinkel ID niet geldig of niet opgegeven -->';
            return;
        }

        require dirname(__FILE__) . '/sidebar.php';
    }
}

new WebwinkelkeurFrontend;
