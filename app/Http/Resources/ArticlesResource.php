<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticlesResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'title' => $this->name,
            'section' => $this->section,
            'image_url' => $this->image_url,
            'category' => $this->category,
            'author' => $this->author,
            'source' => $this->source,
            'source_url' => $this->source_url,
            'published_at' => $this->published_at->toDateTimeString(),
        ];
    }
}
