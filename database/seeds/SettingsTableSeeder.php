<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            ['name' => 'REQUIRE_EMAIL', 'value' => false, 'type' => 'boolean'],
            ['name' => 'APPROVE_ACCOUNTS', 'value' => false, 'type' => 'boolean'],

        ];

        foreach ($settings as $setting) {
            Setting::set($setting['name'], $setting['value'], $setting['type']);
        }
    }
}
