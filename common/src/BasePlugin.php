<?php
namespace Valued\WordPress;

abstract class BasePlugin {

    protected static $instances = [];

    public $admin;
    public $frontend;
    public $woocommerce;

    /** @return string */
    abstract public function getPluginFile();

    /** @return string */
    abstract public function getName();

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

        $this->woocommerce = new WooCommerce($settings);
    }

    public function activatePlugin() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta("
            CREATE TABLE `" . $this->getInviteErrorsTable() . "` (
                `id` int NOT NULL AUTO_INCREMENT,
                `url` varchar(255) NOT NULL,
                `response` text NOT NULL,
                `time` bigint NOT NULL,
                `reported` boolean NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `time` (`time`),
                KEY `reported` (`reported`)
            )
        ");
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

    /** @return string */
    public function getSlug() {
        $file = basename($this->getPluginFile());
        return preg_replace('/\.php$/', '', $file);
    }

}