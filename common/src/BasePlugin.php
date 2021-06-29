<?php
namespace Valued\WordPress;

use ReflectionClass;

abstract class BasePlugin {
    protected static $instances = [];

    public $admin;

    public $frontend;

    public $woocommerce;

    /** @return string */
    abstract public function getSlug();

    /** @return string */
    abstract public function getName();

    /** @return string */
    abstract public function getMainDomain();

    /** @return string */
    abstract public function getDashboardDomain();

    public static function getInstance() {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }
        return self::$instances[static::class];
    }

    public function init() {
        register_activation_hook($this->getPluginFile(), [$this, 'activatePlugin']);
        add_action('plugins_loaded', [$this, 'loadTranslations']);
        add_action('admin_enqueue_scripts', [$this, 'addUpdateNoticeDismissScript']);
        add_action('wp_ajax_' . $this->getUpdateNoticeDismissedAjaxHook(), [$this, 'dismissUpdateNotice']);
        if ($this->shouldDisplayUpdateNotice()) {
            add_action('admin_notices', [$this, 'showUpdateNotice']);
        }
        if (is_admin()) {
            $this->admin = new Admin($this);
        } else {
            $this->frontend = new Frontend($this);
        }

        $this->woocommerce = new WooCommerce($this);
    }

    public function activatePlugin() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->dismissUpdateNotice();

        dbDelta('
            CREATE TABLE `' . $this->getInviteErrorsTable() . '` (
                `id` int NOT NULL AUTO_INCREMENT,
                `url` varchar(255) NOT NULL,
                `response` text NOT NULL,
                `time` bigint NOT NULL,
                `reported` boolean NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `time` (`time`),
                KEY `reported` (`reported`)
            )
        ');
    }

    public function loadTranslations() {
        load_plugin_textdomain(
            'webwinkelkeur',
            false,
            "{$this->getSlug()}/common/languages/"
        );
    }

    /**
     * @param string $name
     * @return string
     */
    public function getOptionName($name) {
        return "{$this->getSlug()}_{$name}";
    }

    /** @return string */
    public function getInviteErrorsTable() {
        return $GLOBALS['wpdb']->prefix . $this->getSlug() . '_invite_error';
    }

    /**
     * @param string $__template
     * @param array $__scope
     * @return string
     **/
    public function render($__template, array $__scope) {
        extract($__scope);
        ob_start();
        require $this->locateTemplate($__template);
        return ob_get_clean();
    }

    private function locateTemplate($template) {
        if (wp_using_themes() && $result = locate_template('webwinkelkeur/' . $template . '.php')) {
            return $result;
        }
        return __DIR__ . '/../templates/' . $template . '.php';
    }

    public function getPluginFile() {
        $reflect = new ReflectionClass($this);
        return dirname(dirname($reflect->getFilename())) . '/' . $this->getSlug() . '.php';
    }

    public function isWoocommerceActivated(): bool {
        return class_exists('woocommerce');
    }

    public function getActiveGtinPlugin() {
        $gtin_handler = new GtinHandler();
        return $gtin_handler->getActivePlugin();
    }

    public function getProductMetaKeys(): array {
        global $wpdb;
        $meta_keys = $wpdb->get_col("
            SELECT DISTINCT(pm.meta_key)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE
                p.post_type = 'product'
                AND pm.meta_key <> ''
                AND pm.meta_value <> ''
        ");
        return array_map(
            function ($value) {
                return [
                    'type' => 'meta_key',
                    'name' => $value,
                    'example_value' => substr($this->getMetaValue($value), 0, 15),
                ];
            },
            $meta_keys
        );
    }

    public function getSelectOptions(string $selected_key, bool $suggested = false): string {
        $options = '';
        foreach ($this->getProductKeys() as $key) {
            if ($key['suggested'] != $suggested) {
                continue;
            }
            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlentities($key['option_value']),
                $key['option_value'] === $selected_key ? ' selected' : '',
                htmlentities($key['label'])
            );
        }
        return $options;
    }

    private function getProductKeys(): array {
        $custom_keys = array_merge($this->getProductMetaKeys(), $this->getCustomAttributes());
        return array_map(
            function ($value) {
                return [
                    'option_value' => $value['type'] . $value['name'],
                    'label' => $value['name'] . ' (e.g. "' . $value['example_value'] . '")',
                    'suggested' => $this->isValidGtin($value['example_value']),
                ];
            },
            $custom_keys
        );
    }

    private function getMetaValue(string $meta_key) {
        global $wpdb;
        $sql = "
            SELECT meta.meta_value
            FROM {$wpdb->postmeta} meta
            WHERE meta.meta_key = %s
            AND meta.meta_value <> ''
            ORDER BY meta.meta_id DESC
            LIMIT 1;
        ";
        return $wpdb->get_var($wpdb->prepare($sql, $meta_key));
    }

    private function getCustomAttributes(): array {
        global $wpdb;
        $custom_attributes = [];
        $sql = "
            SELECT meta.meta_id, meta.meta_key as name, meta.meta_value 
            FROM {$wpdb->postmeta} meta
            JOIN {$wpdb->posts} posts
            ON meta.post_id = posts.id 
            WHERE posts.post_type = 'product' 
            AND meta.meta_key='_product_attributes'
            ORDER BY posts.id DESC
            LIMIT 1000;";

        $data = $wpdb->get_results($sql);
        foreach ($data as $value) {
            $product_attr = unserialize($value->meta_value);
            if (!is_array($product_attr)) {
                continue;
            }
            foreach ($product_attr as $arr_value) {
                $custom_attributes[$arr_value['name']] = [
                    'type' => 'custom_attribute',
                    'name' => $arr_value['name'],
                    'example_value' => substr($arr_value['value'], 0, 15),
                ];
            }
        }
        return $custom_attributes;
    }

    private function isValidGtin(string $value): bool {
        return preg_match('/^\d{8}(?:\d{4,6})?$/', $value);
    }

    public function showUpdateNotice() {
        $class = 'notice notice-info is-dismissible ' . $this->getUpdateNoticeClass();
        $message = $this->getUpdateMessage();
        if (!empty($message)) {
            printf('<div class="%s">%s</div>', esc_attr($class), $message);
        }
    }

    private function getUpdateMessage() {
        return $this->getUpdateNotices()[$this->getVersion()] ?? null;
    }

    protected function getUpdateNotices(): array {
        return [];
    }

    public function addUpdateNoticeDismissScript() {
        $js_file = plugin_dir_url(__FILE__) . 'admin/js/update-notice.js';
        $script_name = $this->getOptionName('notice_update');
        wp_register_script(
            $script_name,
            $js_file
        );
        wp_localize_script($script_name, 'notice_params', [
            'class' => $this->getUpdateNoticeClass(),
            'hook' => $this->getUpdateNoticeDismissedAjaxHook(),
        ]);
        wp_enqueue_script($script_name);
    }

    private function getUpdateNoticeClass(): string {
        return $this->getOptionName('custom_notice');
    }

    private function getUpdateNoticeDismissedAjaxHook(): string {
        return $this->getOptionName('notice_dismiss');
    }

    public function dismissUpdateNotice() {
        update_option(
            $this->getOptionName('last_notice_version'),
            $this->getVersion()
        );
    }

    private function shouldDisplayUpdateNotice(): bool {
        return version_compare(
            $this->getVersion(),
            $this->getOption($this->getOptionName('last_notice_version'), ''),
            '>'
        );
    }

    private function getVersion(): string {
        return '$VERSION$';
    }

    private function getDefaultConfig(): array {
        return [
            'invite_delay' => 3,
            'javascript' => true,
            'order_statuses' => WooCommerce::DEFAULT_ORDER_STATUS,
            'product_reviews' => true,
        ];
    }

    public function getOption($name, $default = null) {
        $value = get_option($this->getOptionName($name), null);
        if ($value !== null) {
            return $value;
        }
        $defaults = $this->getDefaultConfig();
        if (isset($defaults[$name])) {
            return $defaults[$name];
        }
        return $default;
    }
}
