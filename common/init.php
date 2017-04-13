<?php

if(!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

return function ($settings) {

    register_activation_hook($settings['PLUGIN_ENTRY'], function () use ($settings) {

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta("
            CREATE TABLE `" . WebwinkelKeurCommon::get_invite_errs_table($settings) . "` (
                `id` int NOT NULL AUTO_INCREMENT,
                `url` varchar(255) NOT NULL,
                `response` text NOT NULL,
                `time` bigint NOT NULL,
                `reported` boolean NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `time` (`time`),
                KEY `reported` (`reported`)
            )
        ");
    });

    require_once dirname(__FILE__) . '/common.php';
    if(is_admin()) {
        require_once dirname(__FILE__) . '/admin.php';
        require $settings['PLUGIN_PATH'] . '/admin.php';
        new $settings['ADMIN_CLASS']($settings);
    } else {
        require_once dirname(__FILE__) . '/frontend.php';
        require $settings['PLUGIN_PATH'] . '/frontend.php';
        new $settings['FRONTEND_CLASS']($settings);
    }

    require_once dirname(__FILE__) . '/woocommerce.php';
    new WebwinkelKeurWooCommerce($settings);
};