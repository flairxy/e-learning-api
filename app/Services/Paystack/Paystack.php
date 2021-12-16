<?php

namespace App\Services\Paystack;


use App\Traits\ConsumesApi;
use Faker\Factory;
use GuzzleHttp\Exception\GuzzleException;

class Paystack
{

    use ConsumesApi;

    private static $__isFaking = false;
    private $baseUri;
    private $secret;
    public $response;

    public function __construct()
    {
        $this->secret = env('PAYSTACK_SECRET');
        $this->baseUri = env('PAYSTACK_ENDPOINT');
    }

    /**
     * Resolve BVN
     * @param $bvn
     * @return bool
     * @throws GuzzleException
     */
    public function verifyBvn($bvn)
    {
        if (self::$__isFaking) {
            return true;
        }

        return $this->request('GET', "bank/resolve_bvn/{$bvn}");
    }

    /**
     * Charge a card
     *
     * @param int $amount Amount in kobo
     * @param string $email
     * @param string $cvv
     * @param string $number
     * @param string $month
     * @param string $year
     * @param string $pin
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function chargeCard($amount, $email, $cvv, $number, $month, $year, $pin = '')
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        $data = [
            'email' => $email,
            'amount' => $amount,
            'card' => [
                'cvv' => $cvv,
                'number' => $number,
                'expiry_month' => $month,
                'expiry_year' => $year
            ]
        ];
        if ($pin) {
            $data['pin'] = $pin;
        }

        return $this->request('POST', "/charge", $data);
    }

    /**
     * Tokenize a card
     *
     * @param string $email
     * @param string $cvv
     * @param string $number
     * @param string $month
     * @param string $year
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function tokenizeCard($email, $number, $cvv, $month, $year)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        $data = [
            'email' => $email,
            'card' => [
                'cvv' => $cvv,
                'number' => $number,
                'expiry_month' => $month,
                'expiry_year' => $year
            ]
        ];

        if ($this->request('POST', "/charge/tokenize", $data)
            && $this->response['data']['reusable']) {
            return $this->response['data']['authorization_code'];
        } else {
            return false;
        }
    }

    /**
     * Charge using authorization code
     * @param $email
     * @param $amount
     * @param $auth_code
     * @param string $pin
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function chargeCardWithAuthCode($email, $amount, $auth_code, $pin = '')
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        $data = [
            'email' => $email,
            'amount' => $amount,
            'authorization_code' => $auth_code
        ];
        if ($pin) {
            $data['pin'] = $pin;
        }

        return $this->request('POST', "/charge", $data);
    }

    /**
     * Submit pin
     *
     * @param $pin
     * @param $reference
     * @return array|bool|mixed|string
     * @throws GuzzleException
     */
    public function submitChargePin($pin, $reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        return $this->request('POST', "/charge/submit_pin", [
            'pin' => $pin,
            'reference' => $reference,
        ]);
    }

    /**
     * Submit OTP
     * @param $otp
     * @param $reference
     * @return array|bool|mixed|string
     * @throws GuzzleException
     */
    public function submitChargeOtp($otp, $reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        return $this->request('POST', "/charge/submit_otp", [
            'otp' => $otp,
            'reference' => $reference,
        ]);
    }

    /**
     * Submit phone number
     *
     * @param $phone
     * @param $reference
     * @return array|bool|mixed|string
     * @throws GuzzleException
     */
    public function submitChargePhone($phone, $reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        return $this->request('POST', "/charge/submit_phone", [
            'phone' => $phone,
            'reference' => $reference,
        ]);
    }

    /**
     * Submit birthday
     *
     * @param $birthday
     * @param $reference
     * @return array|bool|mixed|string
     * @throws GuzzleException
     */
    public function submitChargeBirthday($birthday, $reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        return $this->request('POST', "/charge/submit_birthday", [
            'birthday' => $birthday,
            'reference' => $reference,
        ]);
    }

    /**
     * Check charge status
     *
     * @param $reference
     * @return array|bool|mixed|string
     * @throws GuzzleException
     */
    public function checkPendingCharge($reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('charge');
        }

        return $this->request('GET', "/charge/{$reference}");
    }

    /**
     * Refund a charge
     *
     * @param $reference
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function refund($reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData();
        }

        return $this->request('POST', "/refund", ['transaction' => $reference]);
    }

    /**
     * Verify a transaction
     * See https://developers.paystack.co/v2.0/reference#verify-transaction
     *
     * @param $reference
     * @return bool
     * @throws GuzzleException
     */
    public function verify($reference)
    {
        if (self::$__isFaking) {
            return $this->fakeData('verify');
        }

        if ($this->request('GET', "/transaction/verify/{$reference}")) {
            return $this->response['data']['status'] == 'success';
        } else {
            return false;
        }
    }


    /**
     * Create a recipient
     * @param $name
     * @param $number
     * @param $bank_code
     * @return bool|string
     * @throws GuzzleException
     */
    public function createRecipientFromAccount($name, $number, $bank_code)
    {
        if (self::$__isFaking) {
            return $this->fakeData('recipient');
        }

        $data = [
            'type' => 'nuban',
            'name' => $name,
            'account_number' => $number,
            'bank_code' => $bank_code,
        ];

        if ($this->request('POST', "/transferrecipient", $data)) {
            return $this->response['data']['recipient_code'];
        } else {
            return false;
        }
    }

    /**
     * Transfer to a recipient.
     * This endpoint requires that you disable OTP for transfers.
     * See https://developers.paystack.co/v2.0/reference#disable-otp-requirement-for-transfers
     * @param $amount
     * @param $recipient_id
     * @param bool $checkOtp throw error if OTP is required
     * @return bool|mixed
     * @throws GuzzleException
     * @throws \Exception
     */
    public function transferToRecipient($amount, $recipient_id, $checkOtp = true)
    {
        if (self::$__isFaking) {
            return $this->fakeData();
        }

        $data = [
            'source' => 'balance',
            'amount' => $amount,
            'recipient' => $recipient_id,
        ];
        $result = $this->request('POST', "/transfer", $data);
        if ($checkOtp && $this->response['status'] && $this->response['data']['status'] == 'otp') {
            throw new \Exception('OTP required, please disable otp requirement and try again');
        }

        return $result;
    }

    /**
     * Make a request to Paystack
     * @param $method
     * @param $requestUrl
     * @param array $formParams
     * @param array $headers
     * @return mixed
     * @throws GuzzleException
     */
    public function request($method, $requestUrl, $formParams = [], $headers = [])
    {
        $response = $this->consumeApi($method, $requestUrl, $formParams, $headers);
        $this->response = $this->getApiResponseArray($response);

        return $this->response['status'];
    }

    public static function fake()
    {
        self::$__isFaking = true;
    }

    private function fakeData($type = null, $data = null)
    {

        $faker = Factory::create();
        switch ($type) {
            case 'charge':
                $this->response = [
                    'status' => true,
                    'message' => 'Charge attempted',
                    'data' => [
                        'status' => 'success',
                        "reference" => $faker->md5,
                        'authorization' => [
                            "authorization_code" => $faker->md5,
                            "reusable" => true,
                        ]
                    ],
                ];
                return true;
            case 'tokenize':
                $this->response = [
                    'status' => true,
                    'message' => 'Sample message',
                    'data' => ['reusable' => true, 'authorization_code' => $faker->md5]
                ];
                return $this->response['data']['authorization_code'];
            case 'recipient':
                $this->response = [
                    'status' => true,
                    'message' => 'Sample message',
                    'data' => ['status' => 'success', 'recipient_code' => $faker->md5]
                ];
                return $this->response['data']['recipient_code'];
            case 'verify':
                $this->response = [
                    'status' => true,
                    'message' => 'Sample message',
                    'data' => ['status' => 'success',
                        'ref' => $faker->md5,
                        'amount' => 5000,
                        'id' => 1]
                ];
                return true;
            default:
                $this->response = [
                    'status' => true,
                    'message' => 'Sample message',
                    'data' => ['status' => 'success', 'ref' => $faker->md5]
                ];
                return true;
        }
    }

}
