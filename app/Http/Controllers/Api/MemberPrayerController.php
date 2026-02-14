<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberPrayerResource;
use App\Models\MemberPrayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberPrayerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $prayers = MemberPrayer::query()
            ->with('user')
            ->latest()
            ->paginate();

        return MemberPrayerResource::collection($prayers);
    }

    public function store(Request $request): MemberPrayerResource
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:500'],
            'is_anonymous' => ['boolean'],
        ]);

        $prayer = MemberPrayer::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'likes_count' => 0,
        ]);

        $prayer->load('user');

        return new MemberPrayerResource($prayer);
    }

    public function amen(Request $request, MemberPrayer $prayer): JsonResponse
    {
        $user = $request->user();

        if ($prayer->supports()->where('user_id', $user->id)->exists()) {
            $prayer->supports()->detach($user->id);
            $prayer->decrement('likes_count');
            $liked = false;
        } else {
            $prayer->supports()->attach($user->id);
            $prayer->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $prayer->likes_count,
        ]);
    }
}
