<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('UsersTableSeeder');
        $this->call('LevelsTableSeeder');
        $this->call('SettingsTableSeeder');
        $this->call('TagsTableSeeder');
        $this->call('CategoriesTableSeeder');
        $this->call('CoursesTableSeeder');
    }
}
