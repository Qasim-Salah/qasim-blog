<?php

namespace App\Http\Resources\General;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'title'             => $this->title,
            'slug'              => $this->slug,
            'url'               => route('frontend.posts.show', $this->slug),
            'description'       => $this->description,
            'status'            => $this->status(),
            'image' => $this->image,
            'comment_able'      => $this->comment_able,
            'create_date'       => $this->created_at->format('d-m-Y h:i a'),
            'author'            => new UsersResource($this->user),
            'category'          => new CategoriesResource($this->category),
            'comments_count'    => $this->comments->active()->count(),

        ];
    }
}
