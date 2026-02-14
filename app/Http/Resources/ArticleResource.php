<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $effectivePublishedAt = $this->published_at;
        $thumbnailUrl = MediaUrl::resolve($request, $this->thumbnail);

        if ($effectivePublishedAt === null || ($this->created_at !== null && $effectivePublishedAt->lt($this->created_at))) {
            $effectivePublishedAt = $this->created_at;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'thumbnail' => $thumbnailUrl,
            'published_at' => $effectivePublishedAt?->toISOString(),
        ];
    }
}
