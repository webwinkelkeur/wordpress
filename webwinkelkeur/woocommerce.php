<?php

require_once dirname(__FILE__) . '/api.php';

class WebwinkelKeurWooCommerce {
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'order_completed'), 10, 1);
    }

    public function order_completed($order_id) {
        global $wpdb;

        // invites enabled?
        if(!get_option('webwinkelkeur_invite'))
            return;

        // noremail?
        $noremail = get_option('webwinkelkeur_invite') == 2;

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
        $email = get_post_meta($order_id, '_billing_email', true);
        if(!preg_match('|@|', $email))
            return;

        // send invite
        $api = new WebwinkelKeurAPI($shop_id, $api_key);
        try {
            $api->invite($order_id, $email, $invite_delay, $noremail);
        } catch(WebwinkelKeurAPIAlreadySentError $e) {
            // that's okay
        } catch(WebwinkelKeurAPIError $e) {
            $wpdb->insert($wpdb->prefix . 'webwinkelkeur_invite_error', array(
                'url'       => $e->getURL(),
                'response'  => $e->getMessage(),
                'time'      => time(),
            ));
            $this->insert_comment($order_id, __('WebwinkelKeur uitnodiging kon niet worden verstuurd.') . ' ' . $e->getMessage());
        }
    }

    private function insert_comment($order_id, $content) {
        wp_insert_comment(array(
            'comment_post_ID'   => $order_id,
            'comment_author'    => 'WebwinkelKeur',
            'comment_content'   => $content,
            'comment_agent'     => 'WebwinkelKeur',
            'comment_type'      => 'order_note',
        ));
    }
}

new WebwinkelKeurWooCommerce;
