<?php

use App\Models\Account;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $user = new User();
        $user->first_name = 'Admin';
        $user->last_name = 'Admin';
        $user->email = 'superadmin@localhost';
        $user->username = 'admin';
        $user->admin = true;
        $user->status = 2;
        $user->email_verified = Carbon::now();
        $user->phone = $faker->phoneNumber;
        $user->password = Hash::make('secret');
        $user->save();

        $user = new User();
        $user->first_name = $faker->firstName;
        $user->last_name = $faker->lastName;
        $user->email = 'ololo@io.com';
        $user->username = 'ololo';
        $user->status = 2;
        $user->tutor = true;
        $user->email_verified = Carbon::now();
        $user->phone = $faker->phoneNumber;
        $user->password = Hash::make('secret');
        $user->save();

        $account = new Account();
        $account->user_id = $user->id;
        $account->save();

        $user = new User();
        $user->first_name = $faker->firstName;
        $user->last_name = $faker->lastName;
        $user->email = 'studentololo@io.com';
        $user->username = 'ololo2';
        $user->status = 2;
        $user->student = true;
        $user->email_verified = Carbon::now();
        $user->phone = $faker->phoneNumber;
        $user->password = Hash::make('secret');
        $user->save();
        $account = new Account();
        $account->user_id = $user->id;
        $account->save();
    }
}
