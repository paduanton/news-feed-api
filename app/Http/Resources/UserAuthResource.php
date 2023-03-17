<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'auth_resource' => [
                "token_type" => $this->auth_resource['token_type'],
                "expires_in" => $this->auth_resource['expires_in']->toDateTimeString(),
                "access_token" => $this->auth_resource['access_token'],
                "created_at" => $this->auth_resource['created_at']->toDateTimeString(),
                "refresh_token" => $this->auth_resource['refresh_token'],
                "remember_token" => $this->auth_resource['remember_token']
            ]
        ];
    }
}
