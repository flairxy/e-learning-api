<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Models\Featured;
use App\Models\Level;
use App\Models\Section;
use App\Models\TopCourse;
use Carbon\Carbon;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Aws\S3\S3Client;

class AdminCourseControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */


    public function testGetApproved()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/approved');

        $this->assertSuccessResponse();
    }

    public function testGetPending()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/pending');

        $this->assertSuccessResponse();
    }

    public function testGetFeatured()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/featured');

        $this->assertSuccessResponse();
    }

    public function testGetTop()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/top');

        $this->assertSuccessResponse();
    }


    public function testGetRejected()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/rejected');

        $this->assertSuccessResponse();
    }

    public function testGetDeleted()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/courses/deleted');

        $this->assertSuccessResponse();
    }


    public function testRevertCourseDelete()
    {
        $faker = Factory::create();
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id, 'deleted_at' => Carbon::now()]);

        $data = [
            'id' => $course->id,
        ];

        $this->sendPatch('admin/courses/revert', $data);
        $this->assertSuccessResponse();
    }

    public function testUpdateCourse()
    {
        $faker = Factory::create();
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
        $status = 2;
        $data = [
            'id' => $course->id,
            'status' => $status
        ];

        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'status' => $data['status'],
        ], $data);
    }


    public function testDeleteCourse()
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

        $s3 = [
            'key' => 'AKIA54HEPGXAZV2HQFVM',
            'secret' => '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',
            'region' => 'eu-west-2',
            'bucket' => 'ivoge-elearn',
        ];

        $client =
            new S3Client([
                'version' => 'latest',
                'region' => $s3['region'],
                'credentials' => [
                    'key' =>  $s3['key'],
                    'secret' =>  $s3['secret'],
                ]
            ]);
        $bucket =  $s3['bucket'];

        $data = [
            'client' => $client,
            'bucket' => $bucket,
            'course' => $course,
        ];
        $this->sendPost('admin/courses/test-delete', $data);
        $this->assertSuccessResponse();
    }

    public function testAddToFeatured()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        $data = [
            'courses' => [$course->id]
        ];

        $this->sendPost('admin/courses/featured/create', $data);
        $this->assertSuccessResponse();
    }

    public function testAddToTop()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        $data = [
            'courses' => [$course->id]
        ];

        $this->sendPost('admin/courses/top/create', $data);
        $this->assertSuccessResponse();
    }

    public function testRemoveFeaturedCourses()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        \factory(Featured::class)->create(['course_id' => $course->id]);

        $data = [
            'courses' => [$course->id]
        ];

        $this->sendPost('admin/courses/featured/delete', $data);
        $this->assertSuccessResponse();
    }

    public function testRemoveTopCourses()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);

        \factory(TopCourse::class)->create(['course_id' => $course->id]);

        $data = [
            'courses' => [$course->id]
        ];

        $this->sendPost('admin/courses/top/delete', $data);
        $this->assertSuccessResponse();
    }
}
