<?php
namespace Valued\WordPress;

use Exception;
use ReflectionMethod;
use RuntimeException;
use WC_Customer;
use WC_Product_Factory;
use WP_Comment_Query;

class WooCommerce {
    const DEFAULT_ORDER_STATUS = ['wc-completed'];

    private $plugin;

    public function __construct(BasePlugin $plugin) {
        $this->plugin = $plugin;
        add_action('woocommerce_order_status_changed', [$this, 'orderStatusChanged'], 10, 3);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'set_order_language']);
        add_action('woocommerce_product_options_sku', [$this, 'addGtinOption']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveGtinOption']);
        add_action('init', [$this, 'activateSyncReviews']);
        register_deactivation_hook($this->plugin->getPluginFile(), [$this, 'deactivateSyncReviews']);
        add_action($this->getReviewsHook(), [$this, 'syncReviews']);
        add_action('wp_ajax_' . $this->getManualSyncAction(), [$this, 'manualReviewSync']);
        add_action('wp_ajax_' . $this->getProductKeysAction(), [$this, 'getProductKeys']);
    }

    public function activateSyncReviews() {
        if (!$this->isSyncedToday() && $this->isProductReviewsEnabled()) {
            add_action('admin_notices', [$this, 'autoSyncNotice']);
        }
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

    public function autoSyncNotice() {
        if (get_admin_page_title() == $this->plugin->getName()) {
            $class = 'notice notice-info';
            $message = __('Automatic product review sync did not run in the last 24 hours. Make sure that you have cron jobs configured, or sync manually.', 'webwinkelkeur');
            printf('<div class="%s"><p>%s</p></div>', esc_attr($class), esc_html($message));
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
        if (!$this->plugin->getOption('invite')) {
            return;
        }

        $api_domain = $this->plugin->getDashboardDomain();
        $shop_id = $this->plugin->getOption('wwk_shop_id');
        $api_key = $this->plugin->getOption('wwk_api_key');

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

        $invite_delay = (int) $this->plugin->getOption('invite_delay');
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
        if ($this->plugin->getOption('invite') == 2) {
            $data['max_invitations_per_email'] = 1;
        }

        $with_order_data = !$this->plugin->getOption('limit_order_data') && is_callable([$order, 'get_data']);
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
        $gtin_handler = new GtinHandler();
        if (
            $gtin_handler->getActivePlugin()
            || !$this->isProductReviewsEnabled()
            || (
                $this->plugin->getOption('custom_gtin')
                && $this->plugin->getOption('custom_gtin') != GtinHandler::META_PREFIX . $this->getGtinMetaKey()
            )
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
        $selected_statuses = $this->plugin->getOption('order_statuses') ?: WooCommerce::DEFAULT_ORDER_STATUS;
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
            $gtin_handler = new GtinHandler();
            $gtin_handler->setGtinMetaKey($this->getGtinMetaKey());
            $gtin_handler->setProduct($product);
            $products[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'url' => get_permalink($product->get_id()),
                'image_url' => get_the_post_thumbnail_url($product->get_id()) ?: null,
                'sku' => $product->get_sku(),
                'gtin' => $gtin_handler->getGtin(
                    $this->plugin->getOption('custom_gtin') ?: null
                ),
                'reviews_allowed' => $product->get_reviews_allowed(),
            ];
        }
        return $products;
    }

    public function manualReviewSync() {
        check_ajax_referer($this->getManualSyncNonce());
        try {
            $details = $this->doSyncReviews(isset($_POST['sync_all']) && $_POST['sync_all'] == 'yes');
            wp_send_json([
                'status' => true,
                'message' => $this->plugin->render('woocommerce_review_sync_status', [
                    'details' => $details,
                ]),
            ]);
        } catch (\Exception $e) {
            wp_send_json([
                'status' => false,
                'message' => htmlentities($e->getMessage()),
            ]);
        }
        wp_die();
    }

    public function syncReviews() {
        try {
            $this->doSyncReviews();
        } catch (\Exception $e) {
        }
    }

    private function doSyncReviews($sync_all = false) {
        if (!$this->isProductReviewsEnabled()) {
            throw new \RuntimeException("Product reviews are disabled");
        }
        if (!$this->plugin->isWoocommerceActivated()) {
            throw new \RuntimeException("WooCommerce is not active");
        }
        $api_domain = $this->plugin->getDashboardDomain();
        $shop_id = $this->plugin->getOption('wwk_shop_id');
        $api_key = $this->plugin->getOption('wwk_api_key');
        $api = new API($api_domain, $shop_id, $api_key);
        if ($sync_all) {
            $last_synced = null;
        } else {
            $last_synced = $this->plugin->getOption('last_synced') ?: null;
        }
        $reviews = $api->getReviews($last_synced);
        if (!$reviews->count()) {
            throw new \RuntimeException(sprintf(
                "No reviews to sync since %s",
                $last_synced ?: "forever"
            ));
        }
        $successes = 0;
        $errors = [];
        foreach ($reviews as $review) {
            try {
                $this->processReview($review);
                $successes++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($last_modified = (string) ($reviews[0]->modified ?? null)) {
            update_option($this->plugin->getOptionName('last_synced'), $last_modified);
        }
        update_option($this->plugin->getOptionName('last_executed_sync'), date(\DateTime::RFC3339));
        return [
            'successes' => $successes,
            'errors' => $errors,
        ];
    }

    private function processReview(\SimpleXMLElement $review) {
        $comment_data = $this->getCommentData($review);
        $comment_id = $this->getExistingComment(
            $comment_data['comment_post_ID'],
            $comment_data['comment_author_email'],
            (int) $review->review_id
        );
        if ((int) $review->deleted) {
            if ($comment_id) {
                if (!wp_delete_comment($comment_id)) {
                    throw new RuntimeException("Could not delete review: {$comment_id}");
                }
            }
        } elseif ($comment_id) {
            $comment_data['comment_ID'] = $comment_id;
            if (wp_update_comment($comment_data) === false) {
                throw new RuntimeException("Could not update review: {$comment_id}");
            }
        } else {
            if (!wp_insert_comment($comment_data)) {
                throw new RuntimeException(
                    "Could not insert review for product: {$comment_data['comment_post_ID']}");
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
            throw new RuntimeException(sprintf("No product with ID {$product_id}"));
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
                'rating' => (int) $review->ratings->overasll,
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

    private function getReviewsHook(): string {
        return "{$this->plugin->getSlug()}_reviews_cron";
    }

    private function getReviewIdMetaKey(): string {
        return "_{$this->plugin->getOptionName('review_id')}";
    }

    private function getGtinMetaKey(): string {
        return "_{$this->plugin->getOptionName('gtin')}";
    }

    public function getManualSyncAction(): string {
        return $this->plugin->getOptionName('manual_sync');
    }

    public function getManualSyncNonce(): string {
        return $this->plugin->getOptionName('manual-sync-data');
    }

    public function getProductKeysAction(): string {
        return $this->plugin->getOptionName('product_keys');
    }

    public function getProductKeys(): array {
        wp_send_json([
            'status' => true,
            'data' => $this->plugin->getProductKeys($_GET['selected_key']),
        ]);
        wp_die();
    }

    public function getNextReviewSync(): string {
        return $this->getReviewSyncDate(wp_next_scheduled($this->getReviewsHook()));
    }

    public function getLastReviewSync(): string {
        return $this->getReviewSyncDate(strtotime(
            $this->plugin->getOption('last_executed_sync')
        ));
    }

    private function getReviewSyncDate($date): string {
        if ($date) {
            return htmlentities(date("Y-m-d H:i:s", $date));
        }
        return __('Not registered.', 'webwinkelkeur');
    }

    private function isProductReviewsEnabled(): bool {
        return $this->plugin->getOption('product_reviews');
    }

    private function isSyncedToday(): bool {
        return strtotime($this->getLastReviewSync()) > strtotime('-24 hours');
    }
}