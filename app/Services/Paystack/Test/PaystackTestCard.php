<?php

namespace App\Services\Paystack\Test;

use Illuminate\Support\Carbon;

class PaystackTestCard
{
    protected $number;
    protected $expiry_month;
    protected $expiry_year;
    protected $cvv;
    protected $pin;
    protected $otp;
    protected $phone;

    /**
     * PaystackTestCard constructor.
     * @param $number
     * @param $expiry_month
     * @param $expiry_year
     * @param $cvv
     * @param $pin
     * @param $otp
     * @param $phone
     */
    private function __construct($number, $expiry_month, $expiry_year, $cvv, $pin = null, $otp = null, $phone = null)
    {
        $this->number = $number;
        $this->expiry_month = $expiry_month;
        $this->expiry_year = $expiry_year;
        $this->cvv = $cvv;
        $this->pin = $pin;
        $this->otp = $otp;
        $this->phone = $phone;
    }

    /**
     * @return PaystackTestCard
     */
    public static function custom()
    {
        return new static(env('PAYSTACK_TEST_CARD_NUMBER'),
            env('PAYSTACK_TEST_CARD_MONTH'),
            env('PAYSTACK_TEST_CARD_YEAR'),
            env('PAYSTACK_TEST_CARD_CVV'),
            env('PAYSTACK_TEST_CARD_PIN'));
    }

    /**
     * @return PaystackTestCard
     */
    public static function bankAuthorizationSimulation()
    {
        $expiryDate = Carbon::now();
        return new static('4084080000000409',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '000');
    }


    /**
     * @return PaystackTestCard
     */
    public static function noValidation()
    {
        $expiryDate = Carbon::now();
        return new static('4084084084084081',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '408');
    }

    /**
     * @return PaystackTestCard
     */
    public static function nonReusablePinOtpValidation()
    {
        $expiryDate = Carbon::now();
        return new static('5060666666666666666',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '123',
            '1234',
            '123456');
    }

    /**
     * @return PaystackTestCard
     */
    public static function reusablePinValidation()
    {
        $expiryDate = Carbon::now();
        return new static('507850785078507812',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '081',
            '1111');
    }

    /**
     * @return PaystackTestCard
     */
    public static function pinPhoneOtpValidation()
    {
        $expiryDate = Carbon::now();
        return new static('50785078507850784',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '884',
            '0000',
            '123456',
            '1234567890');
    }

    /**
     * @return PaystackTestCard
     */
    public static function errorDeclined()
    {
        $expiryDate = Carbon::now();
        return new static('4084080000005408',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '001');
    }

    /**
     * @return PaystackTestCard
     */
    public static function errorNoToken()
    {
        $expiryDate = Carbon::now();
        return new static('507850785078507853',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '082',
            '1111');
    }

    /**
     * @return PaystackTestCard
     */
    public static function error500()
    {
        $expiryDate = Carbon::now();
        return new static('5060660000000064',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '606');
    }

    /**
     * @return PaystackTestCard
     */
    public static function errorTimeout()
    {
        $expiryDate = Carbon::now();
        return new static('506066506066506067',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '060');
    }

    /**
     * @return PaystackTestCard
     */
    public static function errorInvalidPhone()
    {
        $expiryDate = Carbon::now();
        return new static('50785078507850784',
            $expiryDate->format('m'),
            $expiryDate->format('y'),
            '884',
            '0000',
            '123456',
            '1234560');
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @return mixed
     */
    public function getCvv()
    {
        return $this->cvv;
    }

    /**
     * @return mixed
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @return mixed
     */
    public function getOtp()
    {
        return $this->otp;
    }

    /**
     * @return null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getExpiryMonth(): string
    {
        return $this->expiry_month;
    }

    /**
     * @return string
     */
    public function getExpiryYear(): string
    {
        return $this->expiry_year;
    }


}
