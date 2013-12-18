<?php
require_once dirname(__FILE__) . '/vendor/Peschar/URLRetriever.php';
class WebwinkelkeurAPI {
    private $shop_id;
    private $api_key;

    public function __construct($shop_id, $api_key) {
        $this->shop_id = (string) $shop_id;
        $this->api_key = (string) $api_key;
    }

    public function invite($order_id, $email, $delay) {
        $url = $this->buildURL('https://www.webwinkelkeur.nl/api.php', array(
            'id'        => $this->shop_id,
            'password'  => $this->api_key,
            'order'     => $order_id,
            'email'     => $email,
            'delay'     => $delay,
        ));

        $retriever = new Peschar_URLRetriever();
        $response = $retriever->retrieve($url);

        if(!$response) {
            throw new WebwinkelkeurAPIError($url, 'API not reachable.');
        } elseif(preg_match('|^\s*Success:|', $response)) {
            return true;
        } elseif(preg_match('|invite already sent|', $response)) {
            throw new WebwinkelkeurAPIAlreadySentError($url, $response);
        } else {
            throw new WebwinkelkeurAPIError($url, $response);
        }
    }

    private function buildURL($address, $parameters) {
        $query_string = http_build_query($parameters);
        if(strpos($address, '?') === false) {
            return $address . '?' . $query_string;
        } else {
            return $address . '&' . $query_string;
        }
    }
}

class WebwinkelkeurAPIError extends Exception {
    private $url;

    public function __construct($url, $message) {
        $this->url = $url;
        parent::__construct($message);
    }

    public function getURL() {
        return $this->url;
    }
}

class WebwinkelkeurAPIAlreadySentError extends WebwinkelkeurAPIError {}
