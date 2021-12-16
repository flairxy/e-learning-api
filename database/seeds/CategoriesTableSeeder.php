<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $categories = ['Development', 'Business', 'IT & Software', 'Personal Development', 'Design', 'Marketing', 'Photography', 'Health & Fitness', 'Music', 'Teaching & Academics'];
        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
