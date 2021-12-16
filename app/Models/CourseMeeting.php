<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;
use App\Traits\UsesUuid;

class CourseMeeting extends EloquentModel
{
    use UsesUuid;
    protected $guarded = ['id'];
    protected $table = 'course_meetings';
}
