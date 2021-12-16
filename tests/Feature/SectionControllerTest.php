<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Section;
use App\Models\Level;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class SectionControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateSection()
    {
        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        $data = [
            'course_id' => $course->id,
            'name' => $faker->sentence(6)
        ];
        $this->sendPost('instructor/section/create', $data);
        $this->assertSuccessResponse();
    }

    public function testGetSection()
    {
        /** @var User $user */
        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        $data = [
            'course_id' => $course->id,
        ];
        $this->sendPost('instructor/section/show', $data);

        $this->assertSuccessResponse();
    }


    public function testGetSectionById()
    {
        /** @var User $user */
        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);

        $this->sendGet('instructor/section/' . $course->id . '/' . $section->id);

        $this->assertSuccessResponse();
    }


    public function testUpdateSection()
    {

        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);

        $data = [
            'course_id' => $course->id,
            'id' => $section->id,
            'name' => $faker->sentence(6)
        ];

        $this->sendPatch('instructor/section/update', $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'course_id' => $data['course_id'],
            'name' => $data['name'],
        ], $data);
    }


    public function testDeleteSection()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);

        $this->sendDelete('instructor/section/delete/' . $section->id . '/' . $section->course_id);

        $this->assertSuccessResponse();
        $section = Section::find(1);
        $this->assertEmpty($section);
    }
}
