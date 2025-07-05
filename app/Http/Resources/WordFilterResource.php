<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WordFilterResource extends JsonResource
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
            'pattern' => $this->pattern,
            'replacement' => $this->replacement,
            'filter_type' => $this->filter_type,
            'pattern_type' => $this->pattern_type,
            'severity' => $this->severity,
            'is_active' => $this->is_active,
            'case_sensitive' => $this->case_sensitive,
            'applies_to' => $this->applies_to,
            'notes' => $this->notes,
            'creator' => $this->whenLoaded('creator', function () {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ] : null;
            }, null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
