<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

trait UsesUuid
{

    /**
     * The "booting" method of the model, This help to magically create uuid for all new models
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}
