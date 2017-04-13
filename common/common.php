<?php

class WebwinkelKeurCommon {

    protected $settings;

    protected $invite_errs_table;

    public function __construct(array $settings) {
        $this->settings = $settings;
        $this->invite_errs_table = self::get_invite_errs_table($settings);
    }

    protected function get_option_name($name) {
        return $this->settings['PLUGIN_SLUG'] . '_' . $name;
    }

    public static function get_invite_errs_table(array $settings) {
        global $wpdb;
        return $wpdb->prefix . $settings['PLUGIN_SLUG'] . '_invite_error';
    }

}