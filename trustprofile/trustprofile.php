<?php
/*
Plugin Name: TrustProfile
Plugin URI: https://trustprofile.io?ref=wp-plugin
Description: Collect reviews and earn trust badges to show on your webshop. Don't keep losing revenue. Show you can be trusted!
Version: $VERSION$
Author: TrustProfile
Author URI: https://trustprofile.io?ref=wp-author
WC tested up to: 4.99
*/

namespace TrustProfile\WordPress;

require __DIR__ . '/common/autoload.php';
require __DIR__ . '/src/Plugin.php';

Plugin::getInstance()->init();
