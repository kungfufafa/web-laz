<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(string $path)
    {
        $decoded = urldecode($path);
        $normalized = MediaUrl::normalizePath($decoded);

        if ($normalized === '' || str_contains($normalized, '..')) {
            abort(404);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->response($normalized);
        }

        if (Storage::disk('local')->exists($normalized)) {
            return Storage::disk('local')->response($normalized);
        }

        abort(404);
    }
}
