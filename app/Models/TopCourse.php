<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class TopCourse extends EloquentModel
{
    protected $guarded = ['id'];
    protected $table = 'courses_top';
}
