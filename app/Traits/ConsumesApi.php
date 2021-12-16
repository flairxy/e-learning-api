<?php

namespace App\Traits;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

trait ConsumesApi
{
    /**
     * @var Response
     */
    public $guzzleResponse;

    /**
     * @param string $method
     * @param $requestUrl
     * @param array $formParams
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function consumeApi($method, $requestUrl, $formParams = [], $headers = [])
    {
        $headers['Accept'] = 'application/json';
        if (isset($this->authorization)) {
            $headers['Authorization'] = $this->authorization;
        }
        if (!empty($this->headers) && is_array($this->headers)) {
            $headers = array_merge($headers, $this->headers);
        }

        $client = new Client();
        $url = rtrim($this->baseUri, '/') . '/' . trim($requestUrl, '/');
        $this->guzzleResponse = $client->request($method, $url, [
            RequestOptions::FORM_PARAMS => $formParams,
            RequestOptions::HEADERS => $headers,
            /**
             * 8 secs timeout, to make decision
             * Checkout https://stackoverflow.com/questions/164175/what-is-considered-a-good-response-time-for-a-dynamic-personalized-web-applicat
             */
            RequestOptions::CONNECT_TIMEOUT => 8
        ]);

        return $this->guzzleResponse;
    }

    protected function getApiResponseArray()
    {
        $content = $this->guzzleResponse->getBody();
        return \GuzzleHttp\json_decode($content, true);
    }
}
