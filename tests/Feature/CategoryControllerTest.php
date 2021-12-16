<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Category;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CategoryControllerTest extends \Tests\TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateCategory()
    {
        $faker = Factory::create();
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $data = [
            'name' => $faker->sentence(6)
        ];
        $this->sendPost('categories/create', $data);
        $this->assertSuccessResponse();
    }

    public function testGetCategories()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        \factory(Category::class)->create();


        $this->sendGet('categories');

        $this->assertSuccessResponse();
    }


    public function testUpdateCategory()
    {

        $faker = Factory::create();
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();

        $data = [
            'id' => $category->id,
            'name' => $faker->sentence(6)
        ];

        $this->sendPatch('categories/update', $data);
        $this->assertSuccessResponse();

        //Validate
        $user->refresh();
        $this->assertEquals([
            'id' => $data['id'],
            'name' => $data['name'],
        ], $data);
    }


    public function testDeleteCategory()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();
        $this->loginAs($user);

        $category = \factory(Category::class)->create();

        $this->sendDelete('categories/delete/' . $category->id);

        $this->assertSuccessResponse();
        $category = Category::find(1);
        $this->assertEmpty($category);
    }
}
