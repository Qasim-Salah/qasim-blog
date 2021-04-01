<?php

use Illuminate\Database\Seeder;

class RoleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Role::create([
            "id" => 1,
            "name" => 'supervisor',
            "permissions" => '["posts", "pages", "post_comments", "post_categories", "contact_us",
             "users","roles","admins"]',
        ]);
        \App\Models\Role::create([
            "id" => 2,
            "name" => 'admin',
            "permissions" => '["posts", "post_comments", "contact_us"]',
        ]);
    }
}
