<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use App\Notifications\Message;
use Faker\Factory;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;

class NotificationControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testGetNotifications()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'id' => $user->id
        ];
        $this->sendPost('notifications', $data);

        $this->assertSuccessResponse();
    }


    public function testUpdateNotification()
    {

        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $welcome_message = 'Welcome to this course';
        $category = \factory(Category::class)->create();
        $course = \factory(Course::class)->create(['created_by' => $user->id, 'welcome_message' => $welcome_message, 'category_id' => $category]);

        $new_user = \factory(User::class)->create();
        Notification::send($new_user, new Message($welcome_message, $course->created_by));
        $id = DB::table('notifications')->latest()->first()->id;
        $data = [
            'user' => $new_user->id,
            'id' => $id
        ];

        $this->sendPatch('notifications/update', $data);
        $this->assertSuccessResponse();
    }
}
