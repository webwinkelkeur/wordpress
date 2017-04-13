<?php

abstract class WebwinkelKeurFrontendCommon extends WebwinkelKeurCommon {
    protected $wwk_shop_id;
    private $script_printed = false;
    private $enable_rich_snippet = true;

    abstract protected function is_sidebar_inactive();

    abstract protected function get_sidebar_settings();

    public function __construct(array $settings) {
        parent::__construct($settings);
        $this->wwk_shop_id = (int) get_option($this->get_option_name('wwk_shop_id'));
        if(!$this->wwk_shop_id)
            return;

        foreach(array(
            'wp_head',
            'wp_meta',
            'wp_footer',
            'wp_print_scripts',
        ) as $action)
            add_action($action, array($this, 'sidebar'));

        if(get_option($this->get_option_name('rich_snippet'))) {
            add_action('wp_footer', array($this, 'rich_snippet'));
            add_action('woocommerce_before_single_product', array($this, 'disable_rich_snippet'));
        }
    }

    public function sidebar() {
        if($this->script_printed) return;
        $this->script_printed = true;

        if($this->is_sidebar_inactive()) {
            echo '<!-- WebwinkelKeur: sidebar niet geactiveerd -->';
            return;
        }

        $settings = $this->get_sidebar_settings();

        require dirname(__FILE__) . '/sidebar.php';
    }

    public function rich_snippet() {
        if(!$this->enable_rich_snippet)
            return;
        $html = $this->get_rich_snippet();
        if($html) echo $html;
    }

    public function disable_rich_snippet() {
        $this->enable_rich_snippet = false;
    }

    private function get_rich_snippet() {
        $tmp_dir = @sys_get_temp_dir();
        if(!@is_writable($tmp_dir))
            $tmp_dir = '/tmp';
        if(!@is_writable($tmp_dir))
            return;

        $url = sprintf('http://%s/shop_rich_snippet.php?id=%s',
                       $this->settings['API_DOMAIN'],
                       (int) $this->wwk_shop_id);

        $cache_file = $tmp_dir . DIRECTORY_SEPARATOR . $this->settings['PLUGIN_SLUG'] . '_'
            . md5(__FILE__) . '_' . md5($url);

        $fp = @fopen($cache_file, 'rb');
        if($fp)
            $stat = @fstat($fp);

        if($fp && $stat && $stat['mtime'] > time() - 7200
           && ($json = @stream_get_contents($fp))
        ) {
            $data = json_decode($json, true);
        } else {
            $context = @stream_context_create(array(
                'http' => array('timeout' => 3),
            ));
            $json = @file_get_contents($url, false, $context);
            if(!$json) return;

            $data = @json_decode($json, true);
            if(empty($data['result'])) return;

            $new_file = $cache_file . '.' . uniqid();
            if(@file_put_contents($new_file, $json))
                @rename($new_file, $cache_file) or @unlink($new_file);
        }

        if($fp)
            @fclose($fp);
        
        if($data['result'] == 'ok')
            return $data['content'];
    }
}
