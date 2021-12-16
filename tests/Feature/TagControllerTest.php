<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Tag;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class TagControllerTest extends \Tests\TestCase
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
        $this->sendPost('tags/create', $data);
        $this->assertSuccessResponse();
    }

    public function testGetLevels()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        \factory(Tag::class)->create();


        $this->sendGet('tags');

        $this->assertSuccessResponse();
    }


    public function testUpdateLevel()
    {

        $faker = Factory::create();
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $tag = \factory(Tag::class)->create();

        $data = [
            'id' => $tag->id,
            'name' => $faker->sentence(6)
        ];

        $this->sendPatch('tags/update', $data);
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

        $tag = \factory(Tag::class)->create();
        $this->sendDelete('tags/delete/' . $tag->id);

        $this->assertSuccessResponse();
        $tag = Tag::find(1);
        $this->assertEmpty($tag);
    }
}
