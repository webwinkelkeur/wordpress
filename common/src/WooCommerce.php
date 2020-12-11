<?php
namespace Valued\WordPress;

use Exception;
use ReflectionMethod;
use RuntimeException;
use WC_Customer;
use WC_Product_Factory;
use WP_Comment_Query;

class WooCommerce {
    private $plugin;

    public function __construct(BasePlugin $plugin) {
        $this->plugin = $plugin;
        add_action('woocommerce_order_status_changed', [$this, 'orderStatusChanged'], 10, 3);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'set_order_language']);
        add_action('woocommerce_product_options_sku', [$this, 'addGtinOption']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveGtinOption']);
        register_activation_hook($this->plugin->getPluginFile(), [$this, 'activateSyncReviews']);
        register_deactivation_hook($this->plugin->getPluginFile(), [$this, 'deactivateSyncReviews']);
        add_action($this->getReviewsHook(), [$this, 'syncReviews']);
        add_action('wp_ajax_' . $plugin->getManualSyncAction(), [$this, 'manualReviewSync']);
    }

    public function activateSyncReviews() {
        if (!wp_next_scheduled($this->getReviewsHook())) {
            wp_schedule_event(time(), 'twicedaily', $this->getReviewsHook());
        }
    }

    public function deactivateSyncReviews() {
        wp_clear_scheduled_hook($this->getReviewsHook());
    }

    public function orderStatusChanged(int $order_id, string $old_status, string $new_status) {
        if ($this->statusReached($new_status)) {
            $this->sendInvite($order_id);
        }
    }

    public function set_order_language($order_id) {
        if (!get_post_meta($order_id, 'wpml_language') && defined('ICL_LANGUAGE_CODE')) {
            update_post_meta($order_id, 'wpml_language', ICL_LANGUAGE_CODE);
        }
    }

    private function sendInvite($order_id) {
        global $wp_version;

        // invites enabled?
        if (!get_option($this->plugin->getOptionName('invite'))) {
            return;
        }

        $api_domain = $this->plugin->getDashboardDomain();
        $shop_id = get_option($this->plugin->getOptionName('wwk_shop_id'));
        $api_key = get_option($this->plugin->getOptionName('wwk_api_key'));

        if (!$shop_id || !$api_key) {
            return;
        }

        /** @var WC_Order $order */
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        if ($order->get_type() !== 'shop_order') {
            return;
        }

        $order_number = $order->get_order_number();

        $email = get_post_meta($order_id, '_billing_email', true);
        if (!preg_match('|@|', $email)) {
            return;
        }

        if (!apply_filters('webwinkelkeur_request_invitation', true, $order)) {
            return;
        }

        $invite_delay = (int) get_option($this->plugin->getOptionName('invite_delay'));
        if ($invite_delay < 0) {
            $invite_delay = 0;
        }

        $invoice_address = $order->get_address('billing');
        $customer_name = $invoice_address['first_name']
            . ' ' . $invoice_address['last_name'];

        $delivery_address = $order->get_address('shipping');
        $phones = [
            $invoice_address['phone'] ?? null,
            $delivery_address['phone'] ?? null,
        ];

        $lang = get_post_meta($order_id, 'wpml_language', true);

        $data = [
            'order'     => $order_number,
            'email'     => $email,
            'delay'     => $invite_delay,
            'language'  => $lang,
            'client'    => 'wordpress',
            'customer_name' => $customer_name,
            'phone_numbers' => array_values(array_filter(array_unique($phones))),
            'order_total'   => $order->get_total(),
            'plugin_version' => $this->get_plugin_version('webwinkelkeur'),
            'platform_version' => 'wp-' . $wp_version . '-wc-' . $this->get_plugin_version('woocommerce'),
        ];
        if (get_option($this->plugin->getOptionName('invite')) == 2) {
            $data['max_invitations_per_email'] = 1;
        }

        $with_order_data = !get_option($this->plugin->getOptionName('limit_order_data')) && is_callable([$order, 'get_data']);
        if ($with_order_data) {
            $order_arr = $this->get_data($order, []);
            $customer_arr = !empty($order_arr['customer_id']) ? $this->get_data(new WC_Customer($order_arr['customer_id']), []) : [];
            $products = $this->get_product_data($order_arr);
            $order_data = [
                'order' => $order_arr,
                'customer' => $customer_arr,
                'products' => $products,
                'invoice_address' => $invoice_address,
                'delivery_address' => $delivery_address,
            ];

            $data['order_data'] = json_encode($this->filter_data($order_data));
        }

        // send invite
        $api = new API($api_domain, $shop_id, $api_key);

        try {
            $api->invite($data);
        } catch (WebwinkelKeurAPIError $e) {
            $this->logApiError($e);
            $this->insert_comment(
                $order_id,
                sprintf(
                    __('The %s invitation could not be sent.', 'webwinkelkeur'),
                    $this->plugin->getName()
                ) . ' ' . $e->getMessage()
            );
            return;
        }

        $this->insert_comment(
            $order_id,
            sprintf(
                __('An invitation was sent to %s dashboard.', 'webwinkelkeur'),
                $this->plugin->getName()
            )
        );
    }

    public function addGtinOption() {
        if (
            GtinHandler::getActivePlugin()
            || !get_option($this->plugin->getOptionName('product_reviews'))
            || get_option($this->plugin->getOptionName('custom_gtin') != $this->getGtinMetaKey())
        ) {
            return;
        }
        $label = 'GTIN';
        echo '<div class="options_group">';
        woocommerce_wp_text_input([
            'id' => $this->getGtinMetaKey(),
            'label' => $label,
            'placeholder' => '',
            'desc_tip' => true,
            'description' => sprintf(__('Add the %s for this product', 'webwinkelkeur'), $label),
        ]);
        echo '</div>';
    }

    public function saveGtinOption($product) {
        if (isset($_POST[$this->getGtinMetaKey()])) {
            $product->update_meta_data(
                $this->getGtinMetaKey(),
                wc_clean(wp_unslash($_POST[$this->getGtinMetaKey()]))
            );
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
        wp_insert_comment([
            'comment_post_ID'   => $order_id,
            'comment_author'    => $this->plugin->getName(),
            'comment_content'   => $content,
            'comment_agent'     => $this->plugin->getName(),
            'comment_type'      => 'order_note',
        ]);
    }

    private function filter_data($value) {
        if (is_array($value)) {
            return array_map(function ($item) {
                return $this->filter_data($item);
            }, $value);
        }
        try {
            return $this->call_method($value, 'get_data');
        } catch (Exception $e) {
        }
        try {
            return $this->call_method($value, '__toString');
        } catch (Exception $e) {
        }
        if (is_object($value)) {
            return new \stdClass();
        }
        return $value;
    }

    private function get_data($value, $default = null) {
        try {
            return $this->call_method($value, 'get_data');
        } catch (Exception $e) {
            return $default;
        }
    }

    private function call_method($obj, $name) {
        $method = new ReflectionMethod($obj, $name);
        if ($method->getNumberOfRequiredParameters() > 0) {
            throw new RuntimeException('Method requires parameters');
        }
        return @$method->invoke($obj);
    }

    private function statusReached(string $new_status): bool {
        $selected_statuses = get_option($this->plugin->getOptionName('order_statuses')) ?: Admin::DEFAULT_ORDER_STATUS;
        foreach ($selected_statuses as $selected_status) {
            if ($new_status == preg_replace('/^wc-/', '', $selected_status)) {
                return true;
            }
        }
        return false;
    }

    private function get_product_data(array $order_arr) {
        $pf = new WC_Product_Factory();
        $products = [];
        foreach ($order_arr['line_items'] as $line_item) {
            $product = $pf->get_product($line_item['product_id']);
            if (!$product) {
                continue;
            }
            $gtin_handler = new GtinHandler($this->getGtinMetaKey());
            $gtin_handler->setProduct($product);
            $products[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'url' => get_permalink($product->get_id()),
                'image_url' => get_the_post_thumbnail_url($product->get_id()) ?: null,
                'sku' => $product->get_sku(),
                'gtin' => $gtin_handler->getGtin(
                    get_option($this->plugin->getOptionName('custom_gtin')) ?: null
                ),
                'reviews_allowed' => $product->get_reviews_allowed(),
            ];
        }
        return $products;
    }

    public function manualReviewSync() {
        check_ajax_referer($this->plugin->getManualSyncNonce());
        $this->syncReviews();
        echo json_encode(['status' => true]);
        wp_die();
    }

    public function syncReviews() {
        if (!get_option($this->plugin->getOptionName('product_reviews'))
            || !$this->plugin->isWoocommerceActivated()
        ) {
            return;
        }
        $api_domain = $this->plugin->getDashboardDomain();
        $shop_id = get_option($this->plugin->getOptionName('wwk_shop_id'));
        $api_key = get_option($this->plugin->getOptionName('wwk_api_key'));
        $api = new API($api_domain, $shop_id, $api_key);
        try {
            $reviews = $api->getReviews(
                get_option($this->plugin->getOptionName('last_synced')) ?: null
            );
        } catch (WebwinkelKeurAPIError $e) {
            $this->logApiError($e);
            return;
        }
        $this->processReviews($reviews);
        update_option($this->plugin->getOptionName('last_synced'), date(\DateTime::RFC3339));
    }

    private function processReviews(\SimpleXMLElement $reviews) {
        foreach ($reviews as $review) {
            $comment_data = $this->getCommentData($review);
            if (empty($comment_data)) {
                continue;
            }
            $comment_id = $this->getExistingComment(
                $comment_data['comment_post_ID'],
                $comment_data['comment_author_email'],
                (int) $review->review_id
            );
            $deleted = (int) $review->deleted;
            if ($comment_id) {
                $comment_data['comment_ID'] = $comment_id;
                if ($deleted) {
                    wp_delete_comment($comment_id);
                    continue;
                }
                wp_update_comment($comment_data);
            } elseif (!$deleted) {
                wp_insert_comment($comment_data);
            }
        }
    }

    private function getExistingComment(int $post_id, string $author_email, int $review_id) {
        $args = [
            'post_id' => $post_id,
            'author_email' => $author_email,
            'type' => 'review',
            'meta_query' => [
                'key' => $this->getReviewIdMetaKey(),
                'value' => $review_id,
            ],
        ];
        $comments_query = new WP_Comment_Query($args);
        $comments = $comments_query->comments;
        return $comments[0]->comment_ID ?? null;
    }

    private function getCommentData(\SimpleXMLElement $review) {
        $pf = new WC_Product_Factory();
        $product_id = (int) $review->products->product->external_id;
        if (!$pf->get_product($product_id)) {
            return null;
        }
        $author_email = sanitize_text_field((string) $review->email);
        return [
            'comment_post_ID' => $product_id,
            'comment_author' => sanitize_text_field((string) $review->reviewer->name),
            'comment_author_email' => $author_email,
            'comment_content' => sanitize_text_field((string) $review->content),
            'comment_type' => 'review',
            'comment_meta' => [
                $this->getReviewIdMetaKey() => (int) $review->review_id,
                'rating' => (int) $review->ratings->overall,
            ],
            'comment_parent' => 0,
            'user_id' => get_user_by('email', $author_email)->ID ?? 0,
            'comment_date' => date('Y-m-d H:i:s', strtotime((string) $review->review_timestamp)),
            'comment_approved' => (int) $review->valid,
        ];
    }

    private function logApiError(WebwinkelKeurAPIError $e) {
        global $wpdb;
        $wpdb->insert($this->plugin->getInviteErrorsTable(), [
            'url' => $e->getURL(),
            'response' => $e->getMessage(),
            'time' => time(),
        ]);
    }

    public function getReviewsHook(): string {
        return "{$this->plugin->getSlug()}_reviews_cron";
    }

    private function getReviewIdMetaKey(): string {
        return "_{$this->plugin->getOptionName('review_id')}";
    }

    private function getGtinMetaKey(): string {
        return "_{$this->plugin->getOptionName('gtin')}";
    }
}