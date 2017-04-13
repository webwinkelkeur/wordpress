<?php

class WebwinkelKeurFrontend extends WebwinkelKeurFrontendCommon {

    protected function is_sidebar_inactive() {
        return !get_option($this->get_option_name('javascript'));
    }

    protected function get_sidebar_settings() {
        return array(
            '_webwinkelkeur_id' => $this->wwk_shop_id,
        );
    }

}
