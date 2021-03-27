<?php

use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use App\Models\User as UserModel;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker      = Factory::create();
        $comments    = [];
        $users       = collect(UserModel::all()->modelKeys());
        $posts       = collect(PostModel::wherePostType('post')->whereStatus(1)->whereCommentAble(1)->get());

        for($i = 0 ; $i < 1000; $i++) {

            $selected_post = $posts->random();
            $post_date = $selected_post->created_at->timestamp;
            $current_date = Carbon::now()->timestamp;

            $comments[] = [
                'name' => $faker->name,
                'email' => $faker->email,
                'url' => $faker->url,
                'ip_address' => $faker->ipv4,
                'comment' => $faker->paragraph(2, true),
                'status' => rand(0, 1),
                'post_id' => $selected_post->id,
                'user_id' => $users->random(),
                'created_at' => date('Y-m-d H:i:s', rand($post_date, $current_date)),
                'updated_at' => date('Y-m-d H:i:s', rand($post_date, $current_date)),
            ];

        }


        $chunks = array_chunk($comments, 500);
        foreach ($chunks as $chunk) {
            CommentModel::insert($chunk);
        }


    }
}
