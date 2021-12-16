<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends EloquentModel
{

    protected $guarded = ['id'];
    protected $table = 'course_reviews';


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
