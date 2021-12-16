<?php

namespace Tests\Feature;

use App\Http\Controllers\Instructor\ContentController;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Content;
use App\Models\Course;
use App\Models\Section;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Aws\S3\S3Client;

class ContentControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateContent()
    {
        $faker = Factory::create();
        $user = \factory(User::class)->create(['tutor' => 1]);
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);

        $data_content = [
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => $faker->sentence(6),
            'text' => $faker->text,
            'duration' => $faker->numberBetween(2, 20),
        ];
        $this->sendPost('instructor/content/create', $data_content);
        $this->assertSuccessResponse();
    }


    public function testUpdateContent()
    {
        $faker = Factory::create();
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        $content = \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);

        $data = [
            'id' => $content->id,
            'course_id' => $course->id,
            'section_id' => $section->id,
            'title' => $faker->sentence(6),
            'text' => $faker->text,
            'duration' => $faker->numberBetween(2, 20),
        ];

        $this->sendPatch('instructor/content/update', $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'course_id' => $data['course_id'],
            'section_id' => $data['section_id'],
            'title' => $data['title'],
            'text' => $data['text'],
            'duration' => $data['duration'],
        ], $data);
    }


    public function testDeleteContent()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        $content = \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);
        $this->sendDelete('instructor/content/delete/' . $content->id . '/' . $content->section_id . '/' . $content->course_id,);

        $this->assertSuccessResponse();
        $content = Content::latest()->first();
        $this->assertEmpty($content);
    }

    public function testStoreFile()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category]);
        $section = \factory(Section::class)->create(['course_id' => $course->id]);
        $content = \factory(Content::class)->create([
            'section_id' => $section->id,
            'course_id' => $course->id
        ]);

        $filename =  'testfile.png';
        $directory =  'videos';
        $oldKey =  '';
        $id =  $user->id;
        $courseId =  $course->id;
        $contentId =  $content->id;

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
            'oldKey' => $oldKey,
            'client' => $client,
            'bucket' => $bucket,
            'directory' => $directory,
            'filename' => $filename,
            'id' => $id,
            'courseId' => $courseId,
            'contentId' => $contentId
        ];
        $this->sendPost('instructor/content/store-now', $data);
        $this->assertSuccessResponse();
    }

    // public function testCourseFile()
    // {
    //     $user = \factory(User::class)->create();
    //     $this->loginAs($user);

    //     $category = \factory(Category::class)->create();
    //     $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);
    //     $section = \factory(Section::class)->create(['course_id' => $course->id]);
    //     $content = \factory(Content::class)->create([
    //         'section_id' => $section->id,
    //         'course_id' => $course->id
    //     ]);
    //     $faker = Factory::create();

    //     $data = [
    //         'course_id' => $course->id,
    //         'url' => $faker->url,
    //         'content_id' => $content->id,
    //         'video' => true
    //     ];

    //     $this->sendPatch('instructor/course/files/update', $data);
    //     $this->assertSuccessResponse();
    // }
}
