<?php

require_once dirname(__FILE__) . '/api.php';

class WebwinkelKeurWooCommerce extends WebwinkelKeurCommon {
    public function __construct(array $settings) {
        parent::__construct($settings);
        add_action('woocommerce_order_status_completed', array($this, 'order_completed'), 10, 1);
    }

    public function order_completed($order_id) {
        global $wpdb;

        $order = wc_get_order($order_id);
        if(!$order)
            return;

        // order number
        $order_number = $order->get_order_number();

        // invites enabled?
        if(!get_option($this->get_option_name('invite')))
            return;

        // noremail?
        $noremail = get_option($this->get_option_name('invite')) == 2;

        // API credentials
        $shop_id = get_option($this->get_option_name('wwk_shop_id'));
        $api_key = get_option($this->get_option_name('wwk_api_key'));

        if(!$shop_id || !$api_key)
            return;

        // invite delay
        $invite_delay = (int) get_option($this->get_option_name('invite_delay'));
        if($invite_delay < 0)
            $invite_delay = 0;

        // e-mail
        $email = get_post_meta($order_id, '_billing_email', true);
        if(!preg_match('|@|', $email))
            return;

        // billing name
        $customername = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);

        // lang
        $lang = get_post_meta($order_id, 'wpml_language', true);

        // send invite
        $api = new WebwinkelKeurAPI($this->settings['API_DOMAIN'], $shop_id, $api_key);
        try {
            $api->invite($order_number, $email, $invite_delay, $lang, $customername, $noremail);
        } catch(WebwinkelKeurAPIAlreadySentError $e) {
            // that's okay
        } catch(WebwinkelKeurAPIError $e) {
            $wpdb->insert($this->invite_errs_table, array(
                'url'       => $e->getURL(),
                'response'  => $e->getMessage(),
                'time'      => time(),
            ));
            $this->insert_comment($order_id, sprintf(__('The %s invitation could not be sent.', 'webwinkelkeur'), $this->settings['PLUGIN_NAME']) . ' ' . $e->getMessage());
        }
    }

    private function insert_comment($order_id, $content) {
        wp_insert_comment(array(
            'comment_post_ID'   => $order_id,
            'comment_author'    => $this->settings['PLUGIN_NAME'],
            'comment_content'   => $content,
            'comment_agent'     => $this->settings['PLUGIN_NAME'],
            'comment_type'      => 'order_note',
        ));
    }
}
