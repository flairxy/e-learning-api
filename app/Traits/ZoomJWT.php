<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait ZoomJWT
{
    private function generateZoomToken()
    {
        $key = env('ZOOM_API_KEY', '');
        $secret = env('ZOOM_API_SECRET', '');
        $payload = [
            'iss' => $key,
            'exp' => strtotime('+5 minute'),
        ];
        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    private function retrieveZoomUrl()
    {
        return env('ZOOM_API_URL', '');
    }

    private function zoomRequest($type, $path, $body)
    {
        $url = $this->retrieveZoomUrl();
        $jwt = $this->generateZoomToken();

        $client = new \GuzzleHttp\Client(['base_uri' => $url]);

        try {
            $response = $client->request($type, $path, [
                "headers" => [
                    "Authorization" => "Bearer $jwt"
                ],
                'json' => $body
            ]);
            return json_decode($response->getBody());
        } catch (\Exception $e) {
            if (401 == $e->getCode()) {
                $jwt = $this->generateZoomToken();

                $client = new \GuzzleHttp\Client(['base_uri' => $url]);
                $response = $client->request($type, $path, [
                    "headers" => [
                        "Authorization" => "Bearer $jwt"
                    ],
                    'json' => $body
                ]);
                return json_decode($response->getBody());
            } else {
                echo $e->getMessage();
            }
        }
    }

    public function zoomGet(string $path, array $query = [])
    {
        return $this->zoomRequest('GET', $path, $query);
    }

    public function zoomPost(string $path, array $body = [])
    {
        return $this->zoomRequest('POST', $path, $body);
    }

    public function zoomPatch(string $path, array $body = [])
    {
        return $this->zoomRequest('PATCH', $path, $body);
    }

    public function zoomDelete(string $path, array $body = [])
    {
        return $this->zoomRequest('DELETE', $path, $body);
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toZoomTimeFormat : ' . $e->getMessage());
            return '';
        }
    }

    public function toUnixTimeStamp(string $dateTime, string $timezone)
    {
        try {
            $date = new \DateTime($dateTime, new \DateTimeZone($timezone));
            return $date->getTimestamp();
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toUnixTimeStamp : ' . $e->getMessage());
            return '';
        }
    }
}
