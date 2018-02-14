<?php
require_once dirname(__FILE__) . '/vendor/Peschar/URLRetriever.php';
class WebwinkelKeurAPI {
    private $api_domain;
    private $shop_id;
    private $api_key;

    public function __construct($api_domain, $shop_id, $api_key) {
        $this->api_domain = (string)$api_domain;
        $this->shop_id = (string) $shop_id;
        $this->api_key = (string) $api_key;
    }

    public function invite(array $data) {
        $credentials = array(
            'id'   => $this->shop_id,
            'code' => $this->api_key
        );

        $url = $this->buildURL('https://' . $this->api_domain . '/api/1.0/invitations.json', $credentials);

        $retriever = new Peschar_URLRetriever();
        $response = $retriever->retrieve($url, $data);

        if(!$response) {
            throw new WebwinkelKeurAPIError($url, 'API not reachable.');
        }

        $result = json_decode($response);
        if (isset ($result->status) && $result->status == 'success') {
            return true;
        }
        if(preg_match('|already sent|', $result->message)) {
            throw new WebwinkelKeurAPIAlreadySentError($url, $result->message);
        }
        if(preg_match('|limit hit|', $result->message)) {
            throw new WebwinkelKeurAPIAlreadySentError($url, $result->message);
        }
        throw new WebwinkelKeurAPIError(
            $url, isset($result->message) ? $result->message : $response);
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

class WebwinkelKeurAPIError extends Exception {
    private $url;

    public function __construct($url, $message) {
        $this->url = $url;
        parent::__construct($message);
    }

    public function getURL() {
        return $this->url;
    }
}

class WebwinkelKeurAPIAlreadySentError extends WebwinkelKeurAPIError {}
