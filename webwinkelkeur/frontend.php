<?php

class WebwinkelKeurFrontend {
    private $script_printed = false;

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
        if($this->script_printed) return;
        $this->script_printed = true;

        if(!get_option('webwinkelkeur_sidebar')
           && !get_option('webwinkelkeur_tooltip')
           && !get_option('webwinkelkeur_javascript')
        ) {
            echo '<!-- WebwinkelKeur: sidebar niet geactiveerd -->';
            return;
        }

        $wwk_shop_id = (int) get_option('webwinkelkeur_wwk_shop_id');
        if(!$wwk_shop_id) {
            echo '<!-- WebwinkelKeur: webwinkel ID niet geldig of niet opgegeven -->';
            return;
        }

        $settings = array(
            '_webwinkelkeur_id' => $wwk_shop_id,
            '_webwinkelkeur_sidebar' => !!get_option('webwinkelkeur_sidebar'),
            '_webwinkelkeur_tooltip' => !!get_option('webwinkelkeur_tooltip'),
        );

        if($sidebar_position = get_option('webwinkelkeur_sidebar_position'))
            $settings['_webwinkelkeur_sidebar_position'] = $sidebar_position;

        $sidebar_top = get_option('webwinkelkeur_sidebar_top');
        if(is_string($sidebar_top) && $sidebar_top != '')
            $settings['_webwinkelkeur_sidebar_top'] = $sidebar_top;

        require dirname(__FILE__) . '/sidebar.php';
    }
}

new WebwinkelKeurFrontend;
