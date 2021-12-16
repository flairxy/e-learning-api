<?php

namespace Tests\Feature;

use App\Http\Controllers\General\MeetingController;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Level;
use App\Models\Section;
use Carbon\Carbon;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class MeetingControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMeeting()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);

        $faker = Factory::create();
        $data = [
            'topic' => $course->title . ' meeting',
            'join_url' => $faker->url,
            'id' => 23423123,
            'password' => '2fg42',
        ];
        $d_data = json_encode($data);
        MeetingController::testCreateMeeting(json_decode($d_data), $course->id);
        $this->seeInDatabase(
            'course_meetings',
            [
                'join_url' => $data['join_url'],
                'password' => $data['password'],
                'meeting_id' => $data['id'],
                'topic' => $data['topic'],
            ]
        );
    }

    public function testGetMeeting()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);

        $faker = Factory::create();
        $data = [
            'topic' => $course->title . ' meeting',
            'join_url' => $faker->url,
            'id' => 23423123,
            'password' => '2fg42',
        ];
        $d_data = json_encode($data);
        MeetingController::testCreateMeeting(json_decode($d_data), $course->id);
        $meeting = CourseMeeting::latest()->first();
        $this->sendGet('meetings/' . $meeting->id);
        $this->assertSuccessResponse();
    }



    public function testDeleteMeeting()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);

        $faker = Factory::create();
        $data = [
            'topic' => $course->title . ' meeting',
            'join_url' => $faker->url,
            'id' => 23423123,
            'password' => '2fg42',
        ];
        $d_data = json_encode($data);
        MeetingController::testCreateMeeting(json_decode($d_data), $course->id);
        $meeting = CourseMeeting::latest()->first();
        MeetingController::testDeleteMeeting($meeting);
        $this->notSeeInDatabase(
            'course_meetings',
            [
                'join_url' => $data['join_url'],
                'password' => $data['password'],
                'meeting_id' => $data['id'],
                'topic' => $data['topic'],
            ]
        );
    }
}
