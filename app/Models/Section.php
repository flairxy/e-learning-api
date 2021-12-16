<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class Section extends EloquentModel
{
    protected $guarded = ['id'];
    protected $table = 'course_sections';

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
