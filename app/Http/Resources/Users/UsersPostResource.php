<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersPostResource extends JsonResource
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
            'id'                => $this->id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'status'            => $this->status,
            'status_text'       => $this->status(),
            'comment_able'      => $this->comment_able,
            'category'          => new UsersCategoriesResource($this->category),
            'image' => $this->image,
            'comments_count'    => $this->comments->count(),
            'comments'          => UsersPostCommentsResource::collection($this->comments->active()),
        ];
    }
}
