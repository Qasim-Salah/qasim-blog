<?php

use App\Models\Page as PageModel;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        PageModel::create([
            'title'         => 'About Us',
            'description'   => $faker->paragraph,
            'status'        => 1,
            'comment_able'  => 0,
            'post_type'     => 'page',
            'user_id'       => 1,
            'category_id'   => 1,
        ]);

        PageModel::create([
            'title'         => 'Our Vision',
            'description'   => $faker->paragraph,
            'status'        => 1,
            'comment_able'  => 0,
            'post_type'     => 'page',
            'user_id'       => 1,
            'category_id'   => 1,
        ]);



    }
}
