<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use Carbon\Carbon;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AdminUserControllerTest extends \Tests\TestCase
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
        $role = 'all';
        $this->sendGet('admin/users/' . $role . '/approved');

        $this->assertSuccessResponse();
    }

    public function testGetPending()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $role = 'all';
        $this->sendGet('admin/users/' . $role . '/pending');

        $this->assertSuccessResponse();
    }

    public function testGetRejected()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $role = 'all';
        $this->sendGet('admin/users/' . $role . '/rejected');

        $this->assertSuccessResponse();
    }

    public function testGetDeleted()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $role = 'all';
        $this->sendGet('admin/users/' . $role . '/deleted');

        $this->assertSuccessResponse();
    }


    public function testRevertUserDelete()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $new_user = \factory(User::class)->create(['deleted_at' => Carbon::now()]);

        $data = [
            'id' => $new_user->id,
        ];

        $this->sendPatch('admin/users/revert', $data);
        $this->assertSuccessResponse();
    }

    public function testUpdateUser()
    {
        $faker = Factory::create();
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);
        $new_user = \factory(User::class)->create();
        $status = 2;
        $data = [
            'id' => $new_user->id,
            'status' => $status
        ];

        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'status' => $data['status'],
        ], $data);
    }


    public function testDeleteUser()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $new_user = \factory(User::class)->create();
        $type = 'perm';

        $this->sendDelete('admin/users/delete/' . $new_user->id . '/' . $type);

        $this->assertSuccessResponse();
    }
}
