<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class Content extends EloquentModel
{
    protected $guarded = ['id'];
    protected $table = "course_contents";

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function files()
    {
        return $this->hasMany(CourseFile::class);
    }
}
