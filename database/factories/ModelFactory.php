<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Auth\User::class, function (Faker\Generator $faker) {
    static $password, $date;
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'phone' => $faker->phoneNumber,
        'password' => \Illuminate\Support\Facades\Hash::make($password ?: 'secret'),
        'api_token' => md5(uniqid()),
        'username' => $faker->userName,
        'deleted_at' => $date
    ];
});

$factory->define(App\Models\Account::class, function (Faker\Generator $faker) {
    static $user;
    return [
        'user_id' => $user,
    ];
});
$factory->define(App\Models\Course::class, function (Faker\Generator $faker) {
    static $user, $category, $url, $date;
    return [
        'title' => $faker->title,
        'category_id' => $category,
        'created_by' => $user,
        'image_url' => $url,
        'deleted_at' => $date,
    ];
});


$factory->define(App\Models\Section::class, function (Faker\Generator $faker) {
    static $course;
    return [
        'name' => $faker->title,
        'course_id' => $course,
    ];
});

$factory->define(App\Models\Content::class, function (Faker\Generator $faker) {
    static $course, $section;
    return [
        'title' => $faker->title,
        'course_id' => $course,
        'section_id' => $section,
        'duration' => $faker->numberBetween(0, 20),
    ];
});

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Models\Tag::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Models\Level::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Models\Featured::class, function (Faker\Generator $faker) {
    static $course_id;
    return [
        'course_id' => $course_id,
    ];
});

$factory->define(App\Models\TopCourse::class, function (Faker\Generator $faker) {
    static $course_id;
    return [
        'course_id' => $course_id,
    ];
});
