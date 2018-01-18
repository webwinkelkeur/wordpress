<?php
/*
Plugin Name: WebwinkelKeur
Plugin URI: https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/wordpress-module/
Description: De WordPress plugin zorgt voor een eenvoudige integratie van het WebwinkelKeur binnen jouw webwinkel. Hiermee is het heel eenvoudig om de innovatieve <a href="https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/sidebar/">WebwinkelKeur Sidebar</a> in jouw WordPress website of WooCommerce webwinkel te integreren. Wanneer je WooCommerce gebruikt, kunnen er bovendien automatisch uitnodigingen naar je klanten worden gestuurd.
Version: 1.6.1
Author: Albert Peschar
Author URI: https://peschar.net/
*/

$settings = array(
    'PLUGIN_PATH' => __DIR__,
    'PLUGIN_SLUG' => 'webwinkelkeur',
    'PLUGIN_NAME' => 'WebwinkelKeur',
    'PLUGIN_ENTRY' => 'webwinkelkeur/webwinkelkeur.php',
    'API_DOMAIN' => 'dashboard.webwinkelkeur.nl',
    'JS_SIDEBAR_DOMAIN' => 'www.webwinkelkeur.nl',
    'ADMIN_CLASS' => 'WebwinkelKeurAdmin',
    'FRONTEND_CLASS' => 'WebwinkelKeurFrontend'
);

$init = require __DIR__ . '/common/init.php';
$init($settings);
