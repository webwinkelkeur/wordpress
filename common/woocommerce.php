<?php

require_once dirname(__FILE__) . '/api.php';

class WebwinkelKeurWooCommerce extends WebwinkelKeurCommon {
    public function __construct(array $settings) {
        parent::__construct($settings);
        add_action('woocommerce_order_status_completed', array($this, 'order_completed'), 10, 1);
    }

    public function order_completed($order_id) {
        global $wpdb, $wp_version;

        // invites enabled?
        if(!get_option($this->get_option_name('invite')))
            return;

        $api_domain = $this->settings['API_DOMAIN'];
        $shop_id = get_option($this->get_option_name('wwk_shop_id'));
        $api_key = get_option($this->get_option_name('wwk_api_key'));

        if(!$shop_id || !$api_key)
            return;

        /** @var WC_Order $order */
        $order = wc_get_order($order_id);
        if(!$order)
            return;

        $order_number = $order->get_order_number();

        $email = get_post_meta($order_id, '_billing_email', true);
        if(!preg_match('|@|', $email))
            return;

        $invite_delay = (int) get_option($this->get_option_name('invite_delay'));
        if($invite_delay < 0)
            $invite_delay = 0;

        $invoice_address = $order->get_address('billing');
        $customer_name = $invoice_address['first_name']
                         .' '. $invoice_address['last_name'];

        $delivery_address = $order->get_address('shipping');
        $phones = [
            $invoice_address['phone'],
            $delivery_address['phone']
        ];

        $lang = get_post_meta($order_id, 'wpml_language', true);

        $data = array(
            'order'     => $order_number,
            'email'     => $email,
            'delay'     => $invite_delay,
            'language'  => $lang,
            'client'    => 'wordpress',
            'customer_name' => $customer_name,
            'phone_numbers' => array_values(array_filter(array_unique($phones))),
            'order_total'   => $order->get_total(),
            'plugin_version' => $this->get_plugin_version('webwinkelkeur'),
            'platform_version' => 'wp-' . $wp_version . '-wc-' . $this->get_plugin_version('woocommerce')
        );
        if (get_option($this->get_option_name('invite')) == 2) {
            $data['max_invitations_per_email'] = 1;
        }

        $with_order_data = !get_option($this->get_option_name('limit_order_data')) && is_callable([$order, 'get_data']);
        if ($with_order_data) {
            $order_arr = $order->get_data();
            $customer_arr = $order_arr['customer_id'] ? (new WC_Customer($order_arr['customer_id']))->get_data() : [];
            $pf = new WC_Product_Factory();
            foreach ($order_arr['line_items'] as $line_item) {
                $product = $pf->get_product($line_item['product_id']);
                if (!$product) {
                    continue;
                }
                $product_arr = $product->get_data();
                $images = get_attached_media('image', $product->get_id());
                foreach ($images as $image) {
                    $product_arr['product_image'][] = wp_get_attachment_image_src($image->ID, 'full')[0];
                }
            }
            $order_data = [
                'order' => $order_arr,
                'customer' => $customer_arr,
                'products' => $product_arr,
                'invoice_address' => $invoice_address,
                'delivery_address' => $delivery_address,
            ];

            $data['order_data'] = json_encode($this->filter_data($order_data));
        }

        // send invite
        $api = new WebwinkelKeurAPI($api_domain, $shop_id, $api_key);
        try {
            $api->invite($data);
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

    private function get_plugin_version($plugin_name) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/' . $plugin_name);
        $plugin_file = $plugin_name . '.php';

        // If the plugin version number is set, return it
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        }
        return null;
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

    private function filter_data($value) {
        if (is_array($value)) {
            return array_map(function ($item) {
                return $this->filter_data($item);
            }, $value);
        }
        if (is_callable([$value, 'get_data'])) {
            return $this->filter_data(@$value->get_data());
        }
        if (is_callable([$value, '__toString'])) {
            return (string)$value;
        }
        if (is_object($value)) {
            return new \stdClass();
        }
        return $value;
    }
}
