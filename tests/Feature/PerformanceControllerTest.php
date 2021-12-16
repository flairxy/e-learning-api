<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class PerformanceControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testOverview()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'user' => $user->id
        ];
        $this->sendPost('instructor/performance/overview', $data);
        $this->assertSuccessResponse();
    }

    public function testStudents()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'user' => $user->id
        ];
        $this->sendPost('instructor/performance/students', $data);
        $this->assertSuccessResponse();
    }

    public function testReviews()
    {
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'user' => $user->id
        ];
        $this->sendPost('instructor/performance/reviews', $data);
        $this->assertSuccessResponse();
    }
}
