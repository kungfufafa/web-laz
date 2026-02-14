<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnailUrl = MediaUrl::resolve($request, $this->thumbnail)
            ?: ($this->youtube_id ? "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg" : null);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'youtube_id' => $this->youtube_id,
            'description' => $this->description,
            'thumbnail' => $thumbnailUrl,
            'published_at' => $this->created_at,
        ];
    }
}
