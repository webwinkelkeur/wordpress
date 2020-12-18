<?php
namespace Valued\WordPress;

use ReflectionClass;

abstract class BasePlugin {
    const PLUGIN_README_URL = 'https://plugins.svn.wordpress.org/%s/trunk/readme.txt';

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
        add_action('admin_enqueue_scripts', [$this, 'addNoticeDismissScript']);
        add_action('wp_ajax_' . $this->getNoticeDismissHook(), [$this, 'noticeDismissed']);
        if ($this->shouldDisplayNotice()) {
            add_action('admin_notices', [$this, 'showCustomNotice']);
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
        $this->noticeDismissed();
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

    public function get_plugin_version($plugin_name) {
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
        require __DIR__ . '/../templates/' . $__template . '.php';
        return ob_get_clean();
    }

    public function getPluginFile() {
        $reflect = new ReflectionClass($this);
        return dirname(dirname($reflect->getFilename())) . '/' . $this->getSlug() . '.php';
    }

    public function isWoocommerceActivated(): bool {
        return class_exists('woocommerce');
    }

    public function getActiveGtinPlugin() {
        return GtinHandler::getActivePlugin();
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
        return array_map(function ($value) {
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
            AND meta.meta_key='_product_attributes';";

        $data = $wpdb->get_results($sql);
        foreach ($data as $value) {
            $product_attr = unserialize($value->meta_value);
            if (!is_array($product_attr)) {
                continue;
            }
            foreach ($product_attr as $arr_value) {
                if (
                    !$this->isUniqueAttribute($custom_attributes, $arr_value['name']) ||
                    empty($arr_value['value'])
                ) {
                    continue;
                }
                $custom_attributes[] = [
                    'type' => 'custom_attribute',
                    'name' => $arr_value['name'],
                    'example_value' => substr($arr_value['value'], 0, 15),
                ];
            }
        }
        return $custom_attributes;
    }

    private function isUniqueAttribute(array $array, string $value): bool {
        foreach ($array as $item) {
            if (isset($item['name']) && $item['name'] == $value) {
                return false;
            }
        }
        return true;
    }

    private function isValidGtin(string $value): bool {
        return preg_match('/^\d{8}(?:\d{4,6})?$/', $value);
    }

    public function showCustomNotice() {
        $class = 'notice notice-info is-dismissible ' . $this->getCustomNoticeClass();
        $message = $this->getNoticeText();
        if (!empty($message)) {
            printf('<div class="%s"><p>%s</p></div>', esc_attr($class), $message);
        }
    }

    public function getNoticeText() {
        $readme = wp_remote_fopen($this->getPluginReadme());
        $pattern = '/===\sUpgrade\sNotice\s===\s* 
               =\s' . preg_quote($this->get_plugin_version($this->getSlug())) . '\s=\s
               (?:.+\R)?(.+)/x';
        preg_match($pattern, $readme, $matches);
        if (isset($matches[1])) {
               return $this->convertReadmeLinkToHtml($matches[1]);
        }
    }

    public function addNoticeDismissScript() {
        $js_file = plugin_dir_url(__FILE__) . 'admin/js/update-notice.js';
        wp_register_script(
            'notice-update',
            $js_file
        );
        wp_localize_script('notice-update', 'notice_params', [
            'class' => $this->getCustomNoticeClass(),
            'hook'  => $this->getNoticeDismissHook(),
        ]);
        wp_enqueue_script('notice-update');
    }

    public function noticeDismissed() {
        update_option(
            $this->getOptionName('last_notice_version'),
            $this->get_plugin_version($this->getSlug())
        );
    }

    private function convertReadmeLinkToHtml(string $notice_text): string {
        $pattern = '/\[(?<link_label>.+)\]\[(?<link_url>.+)\]/';
        preg_match($pattern, $notice_text, $link_matches);
        if (isset($link_matches['link_label'], $link_matches['link_url'])) {
            $link = sprintf('<a target="_blank" href="%s">%s</a>',
                $link_matches['link_url'],
                $link_matches['link_label']
            );
            return preg_replace($pattern, $link, $notice_text, 1);
        }
        return $notice_text;
    }

    private function getPluginReadme() {
        return sprintf(
            self::PLUGIN_README_URL,
            $this->getSlug()
        );
    }

    private function getCustomNoticeClass() {
        return $this->getOptionName('custom_notice');
    }

    private function getNoticeDismissHook() {
        return $this->getOptionName('notice-dismiss');
    }

    private function shouldDisplayNotice(): bool {
        $last_notice_version = get_option($this->getOptionName('last_notice_version'));
        if (
            $last_notice_version
            && version_compare(
                $this->get_plugin_version($this->getSlug()),
                $last_notice_version,
                '>')
        ) {
            return true;
        }
        return false;
    }
}
