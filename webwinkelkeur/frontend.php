<?php

class WebwinkelKeurFrontend {
    private $wwk_shop_id;
    private $script_printed = false;
    private $enable_rich_snippet = true;

    public function __construct() {
        $this->wwk_shop_id = (int) get_option('webwinkelkeur_wwk_shop_id');
        if(!$this->wwk_shop_id)
            return;

        foreach(array(
            'wp_head',
            'wp_meta',
            'wp_footer',
            'wp_print_scripts',
        ) as $action)
            add_action($action, array($this, 'sidebar'));

        if(get_option('webwinkelkeur_rich_snippet')) {
            add_action('wp_footer', array($this, 'rich_snippet'));
            add_action('woocommerce_before_single_product', array($this, 'disable_rich_snippet'));
        }
    }

    public function sidebar() {
        if($this->script_printed) return;
        $this->script_printed = true;

        if(!get_option('webwinkelkeur_sidebar')
           && !get_option('webwinkelkeur_tooltip')
           && !get_option('webwinkelkeur_javascript')
        ) {
            echo '<!-- WebwinkelKeur: sidebar niet geactiveerd -->';
            return;
        }

        $settings = array(
            '_webwinkelkeur_id' => $this->wwk_shop_id,
            '_webwinkelkeur_sidebar' => !!get_option('webwinkelkeur_sidebar'),
            '_webwinkelkeur_tooltip' => !!get_option('webwinkelkeur_tooltip'),
        );

        if($sidebar_position = get_option('webwinkelkeur_sidebar_position'))
            $settings['_webwinkelkeur_sidebar_position'] = $sidebar_position;

        $sidebar_top = get_option('webwinkelkeur_sidebar_top');
        if(is_string($sidebar_top) && $sidebar_top != '')
            $settings['_webwinkelkeur_sidebar_top'] = $sidebar_top;

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

        $url = sprintf('http://www.webwinkelkeur.nl/shop_rich_snippet.php?id=%s',
                       (int) $this->wwk_shop_id);

        $cache_file = $tmp_dir . DIRECTORY_SEPARATOR . 'WEBWINKELKEUR_'
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

new WebwinkelKeurFrontend;
