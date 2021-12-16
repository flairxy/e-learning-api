<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class UserCourse extends EloquentModel
{
    const PAID = 1;
    protected $guarded = ['id'];
    protected $table = "user_courses";
}
