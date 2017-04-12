<?php

class WebwinkelKeurFrontend extends WebwinkelKeurFrontendCommon {

    protected function is_sidebar_inactive() {
        return !get_option('webwinkelkeur_javascript');
    }

    protected function get_sidebar_settings() {
        return array(
            '_webwinkelkeur_id' => $this->wwk_shop_id,
        );
    }

}
new WebwinkelKeurFrontend;
