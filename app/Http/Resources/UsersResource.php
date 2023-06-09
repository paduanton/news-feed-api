<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'created_at' => is_string($this->created_at) ? $this->created_at : $this->created_at->toDateTimeString(),
            'updated_at' => is_string($this->updated_at) ? $this->updated_at : $this->updated_at->toDateTimeString(),
        ];
    }
}
