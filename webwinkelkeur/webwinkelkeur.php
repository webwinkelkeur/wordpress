<?php
/*
Plugin Name: WebwinkelKeur
Plugin URI: https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/wordpress-module/
Description: De WordPress plugin zorgt voor een eenvoudige integratie van het WebwinkelKeur binnen jouw webwinkel. Hiermee is het heel eenvoudig om de innovatieve <a href="https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/sidebar/">WebwinkelKeur Sidebar</a> in jouw WordPress website of WooCommerce webwinkel te integreren. Wanneer je WooCommerce gebruikt, kunnen er bovendien automatisch uitnodigingen naar je klanten worden gestuurd.
Version: 1.2.1
Author: Albert Peschar
Author URI: https://peschar.net/
*/

if(!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

register_activation_hook('webwinkelkeur/webwinkelkeur.php', 'webwinkelkeur_activate');

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

if(is_admin())
    require dirname(__FILE__) . '/admin.php';
else
    require dirname(__FILE__) . '/frontend.php';

require dirname(__FILE__) . '/woocommerce.php';

require_once dirname(__FILE__) . '/vendor/Peschar/Ping.php';
Peschar_Ping::run('WebwinkelKeur WordPress', dirname(__FILE__) . '/../../..');
