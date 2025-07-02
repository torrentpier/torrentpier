<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmojiCategoryResource extends JsonResource
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
            'title' => $this->title,
            'display_order' => $this->display_order,
            'emojis_count' => $this->whenCounted('emojis'),
            'emojis' => EmojiResource::collection($this->whenLoaded('emojis')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
