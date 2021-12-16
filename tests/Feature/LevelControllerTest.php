<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Level;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class LevelControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateLevel()
    {
        $faker = Factory::create();
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'name' => $faker->sentence(6)
        ];
        $this->sendPost('levels/create', $data);
        $this->assertSuccessResponse();
    }

    public function testGetLevels()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        \factory(Level::class)->create();


        $this->sendGet('levels');

        $this->assertSuccessResponse();
    }


    public function testUpdateLevel()
    {

        $faker = Factory::create();
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $level = \factory(Level::class)->create();

        $data = [
            'id' => $level->id,
            'name' => $faker->sentence(6)
        ];

        $this->sendPatch('levels/update', $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'name' => $data['name'],
        ], $data);
    }


    public function testDeleteLevel()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $level = \factory(Level::class)->create();

        $this->sendDelete('levels/delete/' . $level->id);

        $this->assertSuccessResponse();
        $level = Level::find(1);
        $this->assertEmpty($level);
    }
}
