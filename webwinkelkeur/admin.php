<?php

class WebwinkelKeurAdmin extends WebwinkelKeurAdminCommon {

    protected function get_default_config() {
        return array(
            'invite_delay'     => 3,
            'javascript'       => true,
        );
    }

    protected function get_config_fields() {
        return array(
            'wwk_shop_id',
            'wwk_api_key',
            'invite',
            'invite_delay',
            'javascript',
            'rich_snippet',
        );
    }

}
