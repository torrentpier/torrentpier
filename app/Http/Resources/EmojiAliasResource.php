<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmojiAliasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'emoji' => new EmojiResource($this->whenLoaded('emoji')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
