<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedPreferencesResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'type' => $this->type,
            'created_at' => is_string($this->created_at) ? $this->created_at : $this->created_at->toDateTimeString(),
            'updated_at' => is_string($this->updated_at) ? $this->updated_at : $this->updated_at->toDateTimeString(),
        ];
    }
}
