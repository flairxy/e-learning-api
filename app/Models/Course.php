<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends EloquentModel
{
    use UsesUuid, SoftDeletes;

    const PENDING = 0;
    const REJECTED = 1;
    const APPROVED = 2;

    public $incrementing = false;

    protected $guarded = ['id'];
    protected $searchableColumns = ['title'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tags');
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'course_instructors');
    }


    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function contents()
    {
        return $this->hasManyThrough(Content::class, Section::class);
    }

    public function files()
    {
        return $this->hasManyThrough(CourseFile::class, Content::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'user_courses');
    }

    public function awsPath($url)
    {
        $withCom = strstr($url, '.com/');
        return substr($withCom, 5);
    }
}
