<?php

namespace App\Models\Auth;

use App\Models\Account;
use App\Models\Course;
use App\Models\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Notifications\Notifiable;
use Laravel\Lumen\Auth\Authorizable;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, Notifiable, UsesUuid, SoftDeletes;

    public $incrementing = false;

    const PENDING = 0;
    const REJECTED = 1;
    const APPROVED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'other_names', 'email', 'phone', 'username'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'email_verify_code', 'phone_verify_code'
    ];

    public function account()
    {
        return $this->hasOne(Account::class);
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_instructors');
    }

    public function studentCourses()
    {
        return $this->belongsToMany(Course::class, 'user_courses');
    }
}
