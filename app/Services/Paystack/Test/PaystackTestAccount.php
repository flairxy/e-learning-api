<?php

namespace App\Services\Paystack\Test;

class PaystackTestAccount
{
    protected $number;
    protected $bank_code;
    protected $bank;
    protected $birthday;
    protected $otp;

    /**
     * PaystackTestAccount constructor.
     * @param $number
     * @param $bank_code
     * @param string $bank
     * @param string $birthday
     * @param string $otp
     */
    public function __construct($number, $bank_code, $bank = '', $birthday = '1990-20-01', $otp = '123456')
    {
        $this->number = $number;
        $this->bank_code = $bank_code;
        $this->bank = $bank;
        $this->birthday = $birthday;
        $this->otp = $otp;
    }


    /**
     * Use account details from env
     *
     * @return PaystackTestAccount
     */
    public static function custom()
    {
        return new static(env('PAYSTACK_TEST_ACCOUNT_NUMBER'),
            env('PAYSTACK_TEST_ACCOUNT_BANK_CODE'),
            env('PAYSTACK_TEST_ACCOUNT_BANK_NAME'));
    }

    /**
     * @return PaystackTestAccount
     */
    public static function default()
    {
        return new static('0000000000', '057', 'Zenith Bank');
    }

    /**
     * @return PaystackTestAccount
     */
    public static function receiver()
    {
        return new static('0000000000', '011', 'First Bank of Nigeria');
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
    public function getBankCode()
    {
        return $this->bank_code;
    }

    /**
     * @return string
     */
    public function getBank(): string
    {
        return $this->bank;
    }

    /**
     * @return string
     */
    public function getBirthday(): string
    {
        return $this->birthday;
    }

    /**
     * @return string
     */
    public function getOtp(): string
    {
        return $this->otp;
    }


}
