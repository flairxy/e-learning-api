<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Account extends EloquentModel
{
    protected $fillable = [
        'biography', 'headline', 'image_url'
    ];
    protected $hidden = ['id'];
    protected $table = 'user_accounts';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
