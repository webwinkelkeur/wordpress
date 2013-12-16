<?php

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

        $response = @file_get_contents($url);

        if(!$response) {
            throw new WebwinkelkeurAPIError($url, 'API not reachable.');
        } elseif(!preg_match('|^Success:|', trim($response))
                 && !preg_match('|invite already sent|', trim($response))) {
            throw new WebwinkelkeurAPIError($url, 'API response: ' . $response);
        } else {
            return true;
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
