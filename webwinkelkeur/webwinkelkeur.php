<?php
/*
Plugin Name: Webwinkelkeur
Plugin URI: https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/wordpress-plugin/
Description: De WordPress plugin zorgt voor een eenvoudige integratie van het WebwinkelKeur binnen jouw webwinkel. Hiermee is het heel eenvoudig om de innovatieve <a href="https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/sidebar/">WebwinkelKeur Sidebar</a> in jouw WordPress website of WooCommerce webwinkel te integreren. Wanneer je WooCommerce gebruikt, kunnen er bovendien automatisch uitnodigingen naar je klanten worden gestuurd.
Version: 1.0
Author: Albert Peschar
Author URI: https://peschar.net/
*/

if(!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if(is_admin())
    require dirname(__FILE__) . '/admin.php';
else
    require dirname(__FILE__) . '/frontend.php';
