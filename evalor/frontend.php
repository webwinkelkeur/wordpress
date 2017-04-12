<?php

class WebwinkelKeurFrontend extends WebwinkelKeurFrontendCommon {

    protected function is_sidebar_inactive() {
        return !get_option('webwinkelkeur_sidebar')
            && !get_option('webwinkelkeur_tooltip')
            && !get_option('webwinkelkeur_javascript');

    }

    protected function get_sidebar_settings() {
        $settings = array(
            '_webwinkelkeur_id' => $this->wwk_shop_id,
            '_webwinkelkeur_sidebar' => !!get_option('webwinkelkeur_sidebar'),
            '_webwinkelkeur_tooltip' => !!get_option('webwinkelkeur_tooltip'),
        );

        if($sidebar_position = get_option('webwinkelkeur_sidebar_position'))
            $settings['_webwinkelkeur_sidebar_position'] = $sidebar_position;

        $sidebar_top = get_option('webwinkelkeur_sidebar_top');
        if(is_string($sidebar_top) && $sidebar_top != '')
            $settings['_webwinkelkeur_sidebar_top'] = $sidebar_top;
        return $settings;
    }

}
new WebwinkelKeurFrontend;
