<?php
/*
Plugin Name: WebwinkelKeur
Plugin URI: https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/wordpress-module/
Description: De WordPress plugin zorgt voor een eenvoudige integratie van het WebwinkelKeur binnen jouw webwinkel. Hiermee is het heel eenvoudig om de innovatieve <a href="https://www.webwinkelkeur.nl/webwinkel/mogelijkheden/sidebar/">WebwinkelKeur Sidebar</a> in jouw WordPress website of WooCommerce webwinkel te integreren. Wanneer je WooCommerce gebruikt, kunnen er bovendien automatisch uitnodigingen naar je klanten worden gestuurd.
Version: $VERSION$
Author: Albert Peschar
Author URI: https://peschar.net/
WC tested up to: 4.99
*/

namespace WebwinkelKeur\WordPress;


use Valued\WordPress\PhpCompatibilityCheck;
require __DIR__ . '/common/autoload.php';

if (!PhpCompatibilityCheck::isCompatible('webwinkelkeur')) {
    return;
}

require __DIR__ . '/src/Plugin.php';
Plugin::getInstance()->init();

