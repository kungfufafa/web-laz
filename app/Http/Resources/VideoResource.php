<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use App\Support\YouTube;
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
        $youtubeId = YouTube::extractVideoId($this->youtube_id);
        $thumbnailUrl = MediaUrl::resolve($request, $this->thumbnail)
            ?: ($youtubeId ? "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg" : null);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'youtube_id' => $youtubeId,
            'youtube_url' => $youtubeId ? "https://www.youtube.com/watch?v={$youtubeId}" : null,
            'youtube_embed_url' => $youtubeId ? "https://www.youtube.com/embed/{$youtubeId}" : null,
            'description' => $this->description,
            'thumbnail' => $thumbnailUrl,
            'published_at' => $this->created_at,
        ];
    }
}
