<?php
/**
 * @author Albert Peschar <albert@peschar.net>
 * @version 1.0.0
 */

class Peschar_Ping {
    private $product;
    private $host;
    private $dir;

    public function __construct($product, $dir = null) {
        $this->product = $product;

        if(!empty($dir) && $dir = @realpath($dir))
            $this->dir = $dir;

        if(!empty($_SERVER['HTTP_HOST']))
            $this->host = $_SERVER['HTTP_HOST'];
        elseif(!empty($_SERVER['SERVER_NAME']))
            $this->host = $_SERVER['SERVER_NAME'];
        else
            $this->host = '';
    }

    public static function run($product, $dir = null) {
        $ping = new Peschar_Ping($product, $dir);
        $ping->registerShutdownFunction();
    }

    public function getURL() {
        $query = array(
            'product' => $this->product,
            'host'    => $this->host,
        );

        if(!empty($this->dir))
            $query['dir'] = $this->dir;
        
        return 'http://peschar.net/ping?' . http_build_query($query);
    }

    private function getCacheKey() {
        return 'ping_' . md5($this->getURL());
    }

    public function registerShutdownFunction() {
        register_shutdown_function(array($this, '_shutdown'));
    }

    public function _shutdown() {
        set_error_handler(array($this, '_errorHandler'));

        $tmp_dir = $this->getTempDir();
        if(!$tmp_dir)
            return;

        $cache_file = $tmp_dir . '/' . $this->getCacheKey();
        
        $mtime = @filemtime($cache_file);
        if($mtime && $mtime > time() - 86400)
            return;

        $url = $this->getURL();

        $result = @file_get_contents($url);
        if(trim($result) == 'OK') {
            @touch($cache_file);
        } elseif(function_exists('curl_init')) {
            $curl = @curl_init();
            if(!$curl) return;

            @curl_setopt($curl, CURLOPT_URL, $url);
            @curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $result = @curl_exec($curl);

            if(trim($result) == 'OK')
                @touch($cache_file);
        }
    }

    public function _errorHandler() {
    }

    private function getTempDir() {
        $tmp_dir = @sys_get_temp_dir();
        if($tmp_dir && @is_writable($tmp_dir))
            return $tmp_dir;
        if(@is_writable('/tmp'))
            return '/tmp';
    }
}
