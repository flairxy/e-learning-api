<?php

namespace App\Services;


use App\Traits\ConsumesApi;
use Faker\Factory;

class Jusibe
{

    use ConsumesApi;

    private static $__isFaking = false;
    private $baseUri;
    private $authorization;
    public $response;

    public function __construct()
    {
        //Basic Authorization
        $this->authorization = 'Basic ' . base64_encode(env('JUSIBE_KEY') . ':' . env('JUSIBE_ACCESS'));
        $this->baseUri = env('JUSIBE_ENDPOINT', 'https://jusibe.com/smsapi/');
    }


    /**
     * Make a request to Paystack
     * @param $method
     * @param $requestUrl
     * @param array $formParams
     * @param array $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $requestUrl, $formParams = [], $headers = [])
    {
        $response = $this->consumeApi($method, $requestUrl, $formParams, $headers);
        $this->response = $this->getApiResponseArray();

        return $response->getStatusCode() == 200;
    }

    public static function fake()
    {
        self::$__isFaking = true;
    }

    /**
     * @param int $to (Required) This is the Nigerian GSM number you are sending the SMS to. You are to pass a single GSM number.
     * @param string $from (Required) This is the Sender ID for the SMS that is being sent, maximum of eleven (11) characters.
     * A mobile number or any combination of numbers is not allowed as the Sender ID. You can always use any alphanumeric or alphabetic characters as the Sender ID.
     * @param string $message Required) This is the text message that you want to send
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSMS($to, $message, $from = null)
    {
        $from = $from ?: env('JUSIBE_FROM', config('app.name'));
        $params = ['to' => $to, 'from' => $from, 'message' => $message];
        if (self::$__isFaking) {
            return $this->fakeData('send_sms', $params);
        }

        $this->request('POST', "/send_sms", $params);
        return isset($this->response['status'])
            ? ($this->response['status'] == 'Sent')
            : false;
    }

    /**
     * Allows you to get the available SMS credits left in your Jusibe account
     *
     * @return bool|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCredits()
    {
        if (self::$__isFaking) {
            return $this->fakeData('get_credits');
        }

        $this->request('GET', "/get_credits");
        return $this->response['sms_credits'] ?? null;
    }

    /**
     * Allows you to check the delivery status of a sent SMS
     * @param string $messageID (Required) The message ID that was returned when the SMS was sent initially
     * @return bool|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deliveryStatus($messageID)
    {
        $params = ['message_id' => $messageID];
        if (self::$__isFaking) {
            return $this->fakeData('delivery_status', $params);
        }

        $this->request('GET', "/delivery_status", $params);
        return isset($this->response['status'])
            ? ($this->response['status'] == 'Delivered')
            : false;
    }

    private function fakeData($type = null, $data = [])
    {
        $faker = Factory::create();
        switch ($type) {
            case 'send_sms':
                $this->response = [
                    'status' => "Sent",
                    'message_id' => $faker->lexify("??????????"),
                    'sms_credits_used' => 1
                ];
                return true;
            case 'get_credits':
                $this->response = [
                    'sms_credits' => "100"
                ];
                return "100";
            case 'delivery_status':
                $this->response = [
                    'status' => "Delivered",
                    'message_id' => $data['message_id'],
                    'date_sent' => "2015-05-19 04:34:48",
                    'date_delivered' => "2015-05-19 04:35:05"
                ];
                return true;
            default:
                return false;
        }
    }
}
