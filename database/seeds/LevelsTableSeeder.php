<?php

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = ['Beginner', 'Intermediate', 'Expert'];
        foreach ($levels as $level) {
            Level::Create(['name' => $level]);
        }
    }
}
