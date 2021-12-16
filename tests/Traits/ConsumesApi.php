<?php

namespace Tests\Traits;

use App\Models\Auth\User;
use Illuminate\Http\Response;

trait ConsumesApi
{
    private $__data;
    private $__user;

    protected function assertSuccessResponse($code = Response::HTTP_OK)
    {
        $this->assertValidResponseStructure();
        $array = $this->getResponseDataAsArray();
        $this->assertEquals($code, $this->response->getStatusCode(), $this->getErrorMessage());
        $this->assertTrue($array['status']);
        $this->assertEquals($array['message'], 'success');
    }

    protected function assertErrorResponse($code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $this->assertValidResponseStructure();
        $array = $this->getResponseDataAsArray();
        $this->assertEquals($code, $this->response->getStatusCode(), $this->getErrorMessage());
        $this->assertFalse($array['status']);
    }


    private function getErrorMessage()
    {
        $array = $this->getResponseDataAsArray();
        if (is_array($array['message'])) {
            return array_reduce($array['message'], function ($index, $content) {
                if (is_array($content)) {
                    $message = '';
                    foreach ($content as $c) {
                        $message .= $c . PHP_EOL;
                    }
                    $content = $message;
                }
                return $content;
            });

        } else {
            return $array['message'];
        }
    }

    public function getResponseDataAsArray()
    {
        if (!$this->__data) {
            /** @var Response $response */
            $response = $this->response;
            $this->__data = json_decode($response->getContent(), true);
        }

        return $this->__data;
    }


    private function assertValidResponseStructure()
    {
        $this->assertResponseStructure([
            'status', 'message', 'data'
        ]);
    }

    protected function assertResponseStructure($array)
    {
        $this->assertJson($this->response->getContent());
        $this->seeJsonStructure($array);
    }

    public function dumpResponse()
    {
        dd($this->getResponseDataAsArray());
    }

    /**
     * @param $method
     * @param $uri
     * @param array $data
     * @param array $headers
     * @return Response
     */
    protected function send($method, $uri, $data = [], $headers = [])
    {
        //Clear cached response
        $this->__data = null;
        //Set authorization token
        if (is_object($this->__user)) {
            $headers['Authorization'] = 'Bearer ' . $this->__user->api_token;
        }

        switch ($method) {
            case 'POST' :
                $response = $this->post($uri, $data, $headers);
                break;
            case 'PUT' :
                $response = $this->put($uri, $data, $headers);
                break;
            case 'PATCH' :
                $response = $this->patch($uri, $data, $headers);
                break;
            case 'DELETE' :
                $response = $this->delete($uri, $data, $headers);
                break;
            default :
                $response = $this->get($uri, $headers);

        }

        return $response;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param mixed $content
     * @return Response
     */
    protected function request($method, $uri, $parameters = [], $files = [], $cookies = [], $server = [], $content = null)
    {
        //Clear cached response
        $this->__data = null;
        //Set authorization token
        if (is_object($this->__user)) {
            $server['HTTP_Authorization'] = 'Bearer ' . $this->__user->api_token;
        }

        $response = $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);

        return $response;
    }

    protected function sendGet($uri, $headers = [])
    {
        return $this->send('GET', $uri, [], $headers);
    }

    protected function sendPost($uri, $data = [], $headers = [])
    {
        return $this->send('POST', $uri, $data, $headers);
    }

    protected function sendPut($uri, $data = [], $headers = [])
    {
        return $this->send('PUT', $uri, $data, $headers);
    }

    protected function sendPatch($uri, $data = [], $headers = [])
    {
        return $this->send('PATCH', $uri, $data, $headers);
    }

    protected function sendDelete($uri, $data = [], $headers = [])
    {
        return $this->send('DELETE', $uri, $data, $headers);
    }

    protected function loginAs(User $user)
    {
        $this->__user = $user;
    }

    protected function getLoggedInUser()
    {
        return $this->__user;
    }

}
