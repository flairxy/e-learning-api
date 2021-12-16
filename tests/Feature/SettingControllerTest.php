<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use Faker\Factory;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;

class SettingControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testGetSettings()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $this->sendGet('admin/settings/index');

        $this->assertSuccessResponse();
    }


    public function testSettingsSet()
    {

        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [

            ['name' => 'SETTINGS_TEST', 'value' => true, 'type' => 'boolean'],
        ];

        $this->sendPost('admin/settings', $data);
        $this->assertSuccessResponse();
    }
}
