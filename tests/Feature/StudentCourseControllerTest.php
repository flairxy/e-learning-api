<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use Laravel\Lumen\Testing\DatabaseMigrations;

class StudentCourseControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */


    public function testStudentCourses()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $data = [
            'id' => $user->id
        ];
        $this->sendPost('student/course', $data);

        $this->assertSuccessResponse();
    }

    public function testStudentCourse()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $data = [
            'id' => $user->id,
            'course_id' => $course->id
        ];
        $this->sendPost('student/course/show', $data);

        $this->assertSuccessResponse();
    }

    public function testStudentBuyCourse()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $data = [
            'id' => $user->id,
            'course_id' => $course->id,
            'amount' => 2000,
            'reference' => 'test-buy',
            'email' => $user->email,
            'type' => 'initial',
            'role' => 'free'
        ];
        $this->sendPost('student/course/buy', $data);

        $this->assertSuccessResponse();
    }


    public function testStudentReviewCourse()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $data = [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'rating' => 4.5,
            'body' => 'Awesome course',
        ];
        $this->sendPost('student/course/review', $data);

        $this->assertSuccessResponse();
    }
}
