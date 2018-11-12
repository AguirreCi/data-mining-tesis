<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);



    	 DB::table('url')->insert([
            'url' => str_random(10),
            'tipo' => str_random(10).'@gmail.com',
        ]);

    }
}
