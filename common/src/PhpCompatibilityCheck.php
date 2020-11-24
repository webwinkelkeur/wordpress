<?php


namespace Valued\WordPress;


class PhpCompatibilityCheck {
    public static function isCompatible($plugin_name) {
        if (version_compare(phpversion(), '7.0.0', '<')) {
            add_action('admin_notices', function ($arguments) use ($plugin_name) {
                $class = 'notice notice-error';
                $message = sprintf(
                    'The %s plugin is not compatible with PHP %s. Please upgrade to PHP 7.0.0 or higher',
                    $plugin_name,
                    phpversion()
                );
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
            });
            return false;
        }
        return true;
    }
}