<?php

require_once dirname(__FILE__) . '/api.php';

class WebwinkelkeurWooCommerce {
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'order_completed'), 10, 1);
    }

    public function order_completed($order_id) {
        global $wpdb;

        // invites enabled?
        if(!get_option('webwinkelkeur_invite'))
            return;

        // API credentials
        $shop_id = get_option('webwinkelkeur_wwk_shop_id');
        $api_key = get_option('webwinkelkeur_wwk_api_key');

        if(!$shop_id || !$api_key)
            return;

        // invite delay
        $invite_delay = (int) get_option('webwinkelkeur_invite_delay');
        if($invite_delay < 0)
            $invite_delay = 0;

        // e-mail
        $email = get_post_meta(18, '_billing_email', true);
        if(!preg_match('|@|', $email))
            return;

        // send invite
        $api = new WebwinkelkeurAPI($shop_id, $api_key);
        try {
            $api->invite($order_id, $email, $invite_delay);
        } catch(WebwinkelkeurAPIError $e) {
            $wpdb->insert($wpdb->prefix . 'webwinkelkeur_invite_error', array(
                'url'       => $e->getURL(),
                'response'  => $e->getMessage(),
                'time'      => time(),
            ));
        }
    }
}

new WebwinkelkeurWooCommerce;
