<?php
/*
Plugin Name: eValor
Plugin URI: https://www.evalor.es/tienda/modulo/wordpress/
Description: El módulo WordPress facilita la integración del innovador sidebar de evalor en su web WordPress o tienda WooCommerce.  Para los usuarios del WooCommerce Plugin para WordPress el módulo se encarga de invitar de forma automática a los clientes para que compartan su experiencia. Esta funcion solo esta disponible para clientes PLUS.  Cuando el proceso del pedido ha finalizado se envia automaticamente una invitacion al cliente para que comparta su experiencia. Asi se genera confianza y aumenta la conversion de su tienda online. 
Version: 1.1.2
Author: Albert Peschar
Author URI: https://peschar.net/
*/

$settings = array(
    'PLUGIN_PATH' => __DIR__,
    'PLUGIN_SLUG' => 'evalor',
    'PLUGIN_NAME' => 'eValor',
    'PLUGIN_ENTRY' => 'evalor/evalor.php',
    'API_DOMAIN' => 'www.evalor.es',
    'ADMIN_CLASS' => 'EvalorAdmin',
    'FRONTEND_CLASS' => 'EvalorFrontend'
);

$init = require __DIR__ . '/common/init.php';
$init($settings);

function evalor_load_plugin_text_domain() {
    load_plugin_textdomain( 'webwinkelkeur', false, '/evalor/common/languages/' );
}
add_action( 'plugins_loaded', 'evalor_load_plugin_text_domain' );