<?php

use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Featured;
use App\Models\Level;
use App\Models\TopCourse;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $user = User::whereTutor(1)->latest()->first();
        $categories = Category::all();
        foreach ($categories as $category) {
            for ($i = 1; $i <= 6; $i++) {
                $level = Level::latest()->first();
                $course = \factory(Course::class)->create(['created_by' => $user->id, 'category_id' => $category->id]);

                $data = [
                    'title' => $faker->sentence(4),
                    'subtitle' => $faker->text(120),
                    'description' => $faker->text,
                    'language' => $faker->languageCode,
                    'level_id' => $level->id,
                    'status' => 2,
                    'instructors' => [["first_name" => $user->first_name, "last_name" => $user->last_name, "username" => $user->username]],
                    'welcome_message' => $faker->text,
                    'congratulation_message' => $faker->text,
                    'pricing' => intval($faker->numberBetween(0, 200)),
                    'overview' => $faker->text,
                    'prerequisites' => $faker->text,
                    'category_id' => $category->id,
                    'created_by' => $user->id,
                ];


                $course->update($data);
                $even = $i % 2;
                if ($even == 0) {
                    Featured::firstOrCreate([
                        'course_id' => $course->id
                    ]);
                }

                TopCourse::firstOrCreate([
                    'course_id' => $course->id
                ]);
            }
        }
    }
}
