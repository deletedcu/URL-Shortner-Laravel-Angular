<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@site.com',
                'password' => bcrypt('password'),
                'email_verified' => '1',
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            ], [
                'id' => 2,
                'name' => 'user',
                'email' => 'user@site.com',
                'password' => bcrypt('password'),
                'email_verified' => '1',
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            ],
        ]);
    }
}
