<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class Tag extends EloquentModel
{
    protected $guarded = ['id'];


    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_tags');
    }
}
