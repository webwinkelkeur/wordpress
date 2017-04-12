<?php

if(!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

register_activation_hook(WEBWINKELKEUR_ACTIVATION_HOOK, 'webwinkelkeur_activate');


function webwinkelkeur_activate() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("
        CREATE TABLE `" . $wpdb->prefix . "webwinkelkeur_invite_error` (
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
}

if(is_admin()) {
    require dirname(__FILE__) . '/admin.php';
    require WEBWINKELKEUR_PLUGIN_PATH . '/admin.php';
} else {
    require dirname(__FILE__) . '/frontend.php';
    require WEBWINKELKEUR_PLUGIN_PATH . '/frontend.php';
}

require dirname(__FILE__) . '/woocommerce.php';

require_once dirname(__FILE__) . '/vendor/Peschar/Ping.php';