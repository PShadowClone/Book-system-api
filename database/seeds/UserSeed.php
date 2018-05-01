<?php

use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        \App\User::all()->map(function ($item) use ($faker) {
            $item->latitude = 19.470887882368185;
            $item->longitude = 14.296944921399017;
            $item->update();

        });
    }
}
