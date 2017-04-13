<?php

class EvalorAdmin extends WebwinkelKeurAdminCommon {

    protected function get_default_config() {
        return array(
            'invite_delay'     => 3,
            'sidebar_position' => 'left',
            'tooltip'          => true,
            'javascript'       => true,
        );
    }

    protected function get_config_fields() {
        return array(
            'wwk_shop_id',
            'wwk_api_key',
            'sidebar',
            'sidebar_position',
            'sidebar_top',
            'invite',
            'invite_delay',
            'tooltip',
            'javascript',
            'rich_snippet',
        );
    }

}
