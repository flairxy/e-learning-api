<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class Featured extends EloquentModel
{
    protected $guarded = ['id'];
    protected $table = 'courses_featured';
}
