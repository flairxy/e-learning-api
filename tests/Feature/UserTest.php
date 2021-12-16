<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Auth\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function testUserUpdate()
    {
        $faker = Factory::create();
        $data['first_name'] = $faker->firstName;
        $data['last_name'] = $faker->lastName;
        $data['other_names'] = $faker->firstName;
        $data['email'] = $faker->email;
        $data['username'] = $faker->userName;
        $data['phone'] = $faker->phoneNumber;

        /** @var User $user */
        $user = \factory(User::class)->create();
        \factory(Account::class)->create(['user_id' => $user->id]);
        $this->loginAs($user);

        $this->sendPatch("user", $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'other_names' => $user->other_names,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone
        ], $data);
    }

    public function testUserPasswordUpdate()
    {
        $faker = Factory::create();
        $data['password'] = $faker->password;
        $data['password_confirmation'] = $data['password'];
        $data['old_password'] = 'secret';

        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $this->sendPatch("user/password", $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    public function testUserDelete()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $this->sendDelete("user", []);
        $this->assertSuccessResponse();
        //Validate
        $user = User::find(1);
        $this->assertEmpty($user);
        //        //Validate
        //        $user->refresh();
        //        $this->assertFalse($user->exists);
    }

    public function testFetchUser()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $this->sendGet("user");
        $this->assertSuccessResponse();
        $this->assertResponseStructure(['data' => ['id', 'username', 'email', 'first_name', 'last_name']]);
    }
}
