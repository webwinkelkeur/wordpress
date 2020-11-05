<?php
namespace Valued\WordPress;

use Exception;
use Requests;

class API {
    private $api_domain;

    private $shop_id;

    private $api_key;

    public function __construct($api_domain, $shop_id, $api_key) {
        $this->api_domain = (string) $api_domain;
        $this->shop_id = (string) $shop_id;
        $this->api_key = (string) $api_key;
    }

    public function getReviews(): \SimpleXMLElement {
        $params = [
            'id' => $this->shop_id,
            'code' => $this->api_key,
            'detailed' => true,
        ];
        $url = $this->buildURL('https://' . $this->api_domain . '/api/1.0/product_reviews.xml', $params);
        $response = Requests::get($url);
        $response->throw_for_status();
        return simplexml_load_string($response->body)->reviews->review;
    }

    public function invite(array $data) {
        $credentials = [
            'id'   => $this->shop_id,
            'code' => $this->api_key,
        ];

        $url = $this->buildURL('https://' . $this->api_domain . '/api/1.0/invitations.json', $credentials);

        $response = Requests::post($url, [], $data);
        $response->throw_for_status();

        $result = json_decode($response->body);
        if (isset($result->status) && $result->status == 'success') {
            return true;
        }
        if (preg_match('|already sent|', $result->message)) {
            throw new WebwinkelKeurAPIAlreadySentError($url, $result->message);
        }
        if (preg_match('|limit hit|', $result->message)) {
            throw new WebwinkelKeurAPIAlreadySentError($url, $result->message);
        }
        throw new WebwinkelKeurAPIError(
            $url,
            isset($result->message) ? $result->message : $response->body
        );
    }

    private function buildURL($address, $parameters) {
        $query_string = http_build_query($parameters);
        if (strpos($address, '?') === false) {
            return $address . '?' . $query_string;
        }
        return $address . '&' . $query_string;
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

class WebwinkelKeurAPIAlreadySentError extends WebwinkelKeurAPIError {
}
