<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = ['web design', 'javascript', 'web development'];
        foreach ($tags as $tag) {
            Tag::create(['name' => $tag]);
        }
    }
}
