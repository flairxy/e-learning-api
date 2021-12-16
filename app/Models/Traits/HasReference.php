<?php

namespace App\Models\Traits;


trait HasReference
{

    protected static function bootHasReference()
    {
        static::creating(function ($model) {
            $class = get_class();
            if ($model instanceof $class) {
                $s = explode('\\', $class);
                $classname = array_pop($s);

                //For easy identification
                $start = substr($classname, 0, 3);
                //Uniqueness
                $serial = uniqid();
                //randomness
                $random = random_chars(2, 'u');
                //For validation
                $key = "{$start}{$serial}{$random}";
                $checkSum = static::getReferenceChecksum($key);

                $model->reference = strtoupper("{$key}-{$checkSum}");
            }
            return true;
        });
    }

    public static function findByReference($ref)
    {
        return self::where('reference', $ref)->first();
    }

    public static function validateReference($ref)
    {
        list($key, $checkSum) = explode('-', $ref);
        $newCheckSum = static::getReferenceChecksum($key);
        return $checkSum === $newCheckSum;
    }

    private static function getReferenceChecksum($start)
    {
        $appKey = env('APP_KEY');
        return substr(md5("{$start}{$appKey}"), 0, 8);
    }
}
