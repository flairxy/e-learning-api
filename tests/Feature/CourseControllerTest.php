<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Models\Level;
use App\Models\Section;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CourseControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateCourse()
    {
        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);
        $category = \factory(Category::class)->create();

        $data = [
            'title' => $faker->title,
            'category' => $category->id,
            'created_by' => $user->id,
        ];
        $this->sendPost('instructor/course/create', $data);
        $this->assertSuccessResponse();
    }

    public function testGetCoursesByTutor()
    {
        /** @var User $user */
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);

        $data = [
            'created_by' => $user->id
        ];
        $this->sendPost('instructor/course/', $data);

        $this->assertSuccessResponse();
    }


    public function testGetAllCourses()
    {

        $this->sendGet('courses');

        $this->assertSuccessResponse();
    }

    public function testGetFeaturedCourses()
    {

        $this->sendGet('courses/featured');

        $this->assertSuccessResponse();
    }

    public function testGetACourse()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $data = [
            'course_id' => $course->id
        ];
        $this->sendPost('course/show', $data);

        $this->assertSuccessResponse();
    }


    public function testUpdateCourse()
    {
        $faker = Factory::create();
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $level = \factory(Level::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);

        $data = [
            'title' => $faker->title,
            'subtitle' => $faker->text,
            'description' => $faker->text,
            'language' => $faker->languageCode,
            'level_id' => $level->id,
            'welcome_message' => $faker->text,
            'congratulation_message' => $faker->text,
            'pricing' => $faker->numberBetween(0, 200),
            'overview' => $faker->text,
            'prerequisites' => $faker->text,
            'category' => $category->id,
            'course_id' => $course->id,
            'created_by' => $user->id,
            'tags' => [1, 2],
        ];

        $this->sendPatch('instructor/course/update', $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'description' => $data['description'],
            'language' => $data['language'],
            'level_id' => $data['level_id'],
            'welcome_message' => $data['welcome_message'],
            'congratulation_message' => $data['congratulation_message'],
            'pricing' => $data['pricing'],
            'overview' => $data['overview'],
            'prerequisites' => $data['prerequisites'],
            'category' => $data['category'],
            'course_id' => $data['course_id'],
            'created_by' => $data['created_by'],
            'tags' => $data['tags'],
        ], $data);
    }

    public function testShowInstructorCourse()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $data = [
            'id' => $course->id
        ];
        $this->sendPost('instructor/course/show', $data);
        $this->assertSuccessResponse();
    }

    public function testDeleteCourse()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);

        $this->sendDelete('instructor/course/delete/' . $course->id . '/' . $user->id);

        $this->assertSuccessResponse();
        $course = Course::latest()->first();
        $this->assertEmpty($course);
    }
}
