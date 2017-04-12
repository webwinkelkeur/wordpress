<?php
/*
Plugin Name: WebwinkelKeur
Plugin URI: https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/wordpress-module/
Description: De WordPress plugin zorgt voor een eenvoudige integratie van het WebwinkelKeur binnen jouw webwinkel. Hiermee is het heel eenvoudig om de innovatieve <a href="https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/sidebar/">WebwinkelKeur Sidebar</a> in jouw WordPress website of WooCommerce webwinkel te integreren. Wanneer je WooCommerce gebruikt, kunnen er bovendien automatisch uitnodigingen naar je klanten worden gestuurd.
Version: 1.3.0
Author: Albert Peschar
Author URI: https://peschar.net/
*/

define('WEBWINKELKEUR_PLUGIN_PATH', __DIR__);
define('WEBWINKELKEUR_API_DOMAIN', 'www.webwinkelkeur.nl');
define('WEBWINKELKEUR_ACTIVATION_HOOK', 'webwinkelkeur/webwinkelkeur.php');

require __DIR__ . '/common/init.php';
