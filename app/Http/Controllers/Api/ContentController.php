<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\VideoResource;
use App\Models\Article;
use App\Models\Video;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContentController extends Controller
{
    public function articles(): AnonymousResourceCollection
    {
        $articles = Article::query()
            ->where('is_published', true)
            ->orderByRaw('CASE WHEN published_at IS NULL OR published_at < created_at THEN created_at ELSE published_at END DESC')
            ->paginate();

        return ArticleResource::collection($articles);
    }

    public function article(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    public function videos(): AnonymousResourceCollection
    {
        $videos = Video::query()
            ->where('is_published', true)
            ->latest()
            ->paginate();

        return VideoResource::collection($videos);
    }
}
