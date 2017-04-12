<?php
/*
Plugin Name: eValor
Plugin URI: https://www.evalor.es/tienda/modulo/wordpress/
Description: El módulo WordPress facilita la integración del innovador sidebar de evalor en su web WordPress o tienda WooCommerce.  Para los usuarios del WooCommerce Plugin para WordPress el módulo se encarga de invitar de forma automática a los clientes para que compartan su experiencia. Esta funcion solo esta disponible para clientes PLUS.  Cuando el proceso del pedido ha finalizado se envia automaticamente una invitacion al cliente para que comparta su experiencia. Asi se genera confianza y aumenta la conversion de su tienda online. 
Version: 1.1.2
Author: Albert Peschar
Author URI: https://peschar.net/
*/

define('WEBWINKELKEUR_PLUGIN_PATH', __DIR__);
define('WEBWINKELKEUR_API_DOMAIN', 'www.evalor.es');
define('WEBWINKELKEUR_ACTIVATION_HOOK', 'evalor/evalor.php');

require __DIR__ . '/common/init.php';

