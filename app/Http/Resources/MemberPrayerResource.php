<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberPrayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $isAnonymous = (bool) $this->is_anonymous;

        return [
            'id' => $this->id,
            'content' => $this->content,
            'user' => $isAnonymous ? null : [
                'id' => $user?->id,
                'name' => $user?->name,
                'avatar_url' => $user ? MediaUrl::resolve($request, $user->avatar_url) : null,
            ],
            'user_name' => $isAnonymous ? 'Hamba Allah' : ($user?->name ?? 'Hamba Allah'),
            'is_anonymous' => $isAnonymous,
            'likes_count' => $this->likes_count,
            'is_liked_by_me' => $request->user() ? $this->supports()->where('user_id', $request->user()->id)->exists() : false,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
