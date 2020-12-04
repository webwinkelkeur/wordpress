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

        if (is_admin()) {
            $this->admin = new Admin($this);
        } else {
            $this->frontend = new Frontend($this);
        }

        $this->woocommerce = new WooCommerce($this);
    }

    public function activatePlugin() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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
        ");
        return array_map(
            function ($value) {
                return [
                    'type' => 'meta',
                    'name' => $value
                ];
            },
            $meta_keys
        );
    }

    public function getProductKeys() {
        return array_merge($this->getProductMetaKeys(), $this->getCustomAttributes());
    }

    private function getCustomAttributes(): array {
        global $wpdb;
        $custom_attributes = [];
        $sql = "
            SELECT meta.meta_id, meta.meta_key as name, meta.meta_value 
            FROM {$wpdb->postmeta} meta
            JOIN {$wpdb->posts} posts
            ON meta.post_id = posts.id 
            WHERE posts.post_type LIKE '%product%' 
            AND meta.meta_key='_product_attributes';";

        $data = $wpdb->get_results($sql);
        if (!empty($data)) {
            foreach ($data as $value) {
                $product_attr = unserialize($value->meta_value);
                if (!empty($product_attr)) {
                    foreach ($product_attr as $arr_value) {
                        $custom_attributes[] = [
                            'type' => 'attr',
                            'name' => $arr_value['name']
                        ];
                    }
                }
            }
        }
        return $custom_attributes;
    }
}
