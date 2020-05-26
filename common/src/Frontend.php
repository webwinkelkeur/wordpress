<?php
namespace Valued\WordPress;

class Frontend {
    private $plugin;

    protected $wwk_shop_id;

    private $script_printed = false;

    private $enable_rich_snippet = true;

    protected function is_sidebar_inactive() {
        return !get_option($this->plugin->getOptionName('javascript'));
    }

    protected function get_sidebar_settings() {
        return [
            "_{$this->plugin->getSlug()}_id" => $this->wwk_shop_id,
        ];
    }

    public function __construct(BasePlugin $plugin) {
        $this->plugin = $plugin;

        if (basename($_SERVER['SCRIPT_FILENAME']) == 'wp-login.php') {
            return;
        }

        $this->wwk_shop_id = (int) get_option($this->plugin->getOptionName('wwk_shop_id'));
        if (!$this->wwk_shop_id) {
            return;
        }

        add_shortcode("{$this->plugin->getSlug()}_rich_snippet", function () {
            return $this->get_rich_snippet();
        });

        foreach ([
            'wp_head',
            'wp_meta',
            'wp_footer',
            'wp_print_scripts',
        ] as $action) {
            add_action($action, [$this, 'sidebar']);
        }

        if (get_option($this->plugin->getOptionName('rich_snippet'))) {
            add_action('wp_footer', [$this, 'rich_snippet']);
            add_action('woocommerce_before_single_product', [$this, 'disable_rich_snippet']);
        }
    }

    public function sidebar() {
        if ($this->script_printed) {
            return;
        }
        $this->script_printed = true;

        if ($this->is_sidebar_inactive()) {
            echo "<!-- {$this->plugin->getName()}: sidebar not activated -->";
            return;
        }

        $settings = $this->get_sidebar_settings();

        echo $this->plugin->render('sidebar', [
            'plugin' => $this->plugin,
            'settings' => $settings,
        ]);
    }

    public function rich_snippet() {
        if (!$this->enable_rich_snippet) {
            return;
        }
        $html = $this->get_rich_snippet();
        if ($html) {
            echo $html;
        }
    }

    public function disable_rich_snippet() {
        $this->enable_rich_snippet = false;
    }

    private function get_rich_snippet() {
        $tmp_dir = @sys_get_temp_dir();
        if (!@is_writable($tmp_dir)) {
            $tmp_dir = '/tmp';
        }
        if (!@is_writable($tmp_dir)) {
            return $this->log_error("The temporary directory $tmp_dir is not writable.");
        }

        $url = sprintf(
            'https://%s/webshops/rich_snippet?id=%d',
            $this->plugin->getDashboardDomain(),
            (int) $this->wwk_shop_id
        );

        $cache_file = $tmp_dir . DIRECTORY_SEPARATOR . $this->plugin->getSlug() . '_'
            . md5(__FILE__) . '_' . md5($url);

        $fp = @fopen($cache_file, 'rb');

        if ($fp && ($stat = @fstat($fp)) && $stat['mtime'] > time() - 7200
           && ($json = @stream_get_contents($fp))
        ) {
            $data = json_decode($json, true);
        } else {
            $context = @stream_context_create([
                'http' => ['timeout' => 3],
                'ssl' => ['verify_peer' => false],
            ]);
            $json = @file_get_contents($url, false, $context);
            if (!$json) {
                return $this->log_error("Failed to retrieve rich snippet data from $url");
            }

            $data = @json_decode($json, true);
            if (empty($data['result'])) {
                return $this->log_error("Failed to decode rich snippet data from $url");
            }

            $new_file = $cache_file . '.' . uniqid();
            if (@file_put_contents($new_file, $json)) {
                @rename($new_file, $cache_file) or @unlink($new_file);
            }
        }

        if ($fp) {
            @fclose($fp);
        }

        if (empty($data) || $data['result'] != 'ok') {
            return $this->log_error("Did not get a succesful response from $url");
        }

        return $data['content'];
    }

    private function log_error($message) {
        return sprintf(
            '<script>console.error(%s)</script>',
            json_encode("{$this->plugin->getName()}: $message")
        );
    }
}
