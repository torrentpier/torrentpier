<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmojiResource extends JsonResource
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
            'emoji_text' => $this->emoji_text,
            'emoji_shortcode' => $this->emoji_shortcode,
            'image_url' => $this->image_url,
            'sprite_mode' => $this->sprite_mode,
            'sprite_params' => $this->sprite_params,
            'display_order' => $this->display_order,
            'category' => new EmojiCategoryResource($this->whenLoaded('category')),
            'aliases' => EmojiAliasResource::collection($this->whenLoaded('aliases')),
            'aliases_count' => $this->whenCounted('aliases'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
