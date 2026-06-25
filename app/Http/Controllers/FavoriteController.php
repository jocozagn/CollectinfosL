<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Content $content): JsonResponse
    {
        $user = Auth::user();

        if (! $content->isPublished()) {
            abort(404);
        }

        $relation = $user->favoriteContents();

        if ($relation->where('content_id', $content->id)->exists()) {
            $relation->detach($content->id);

            return response()->json([
                'favorited' => false,
                'slug' => $content->slug,
            ]);
        }

        $relation->attach($content->id);

        return response()->json([
            'favorited' => true,
            'slug' => $content->slug,
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slugs' => ['nullable', 'array'],
            'slugs.*' => ['string', 'max:255'],
        ]);

        $user = Auth::user();
        $slugs = array_values(array_unique($data['slugs'] ?? []));

        if ($slugs !== []) {
            $contentIds = Content::published()
                ->whereIn('slug', $slugs)
                ->pluck('id');

            $user->favoriteContents()->syncWithoutDetaching($contentIds);
        }

        return response()->json([
            'slugs' => $user->favoriteContents()->pluck('slug')->all(),
        ]);
    }
}
