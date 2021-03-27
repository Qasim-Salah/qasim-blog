<?php

use App\Models\Category as CategoryModel;
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
        CategoryModel::create(['name' => 'un-categorized', 'status' => 1]);
        CategoryModel::create(['name' => 'Natural', 'status' => 1]);
        CategoryModel::create(['name' => 'Flowers', 'status' => 1]);
        CategoryModel::create(['name' => 'Kitchen', 'status' => 0]);

    }
}
