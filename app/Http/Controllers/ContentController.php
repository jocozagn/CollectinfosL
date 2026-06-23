<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Content::published()->latest('published_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('categorie')) {
            $query->where('category', $request->categorie);
        }

        if ($request->filled('theme')) {
            $query->where('theme', $request->theme);
        }

        if ($request->filled('access')) {
            $query->where('access', $request->access);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('summary', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }

        $contents = $query->with('translations')->paginate(12)->withQueryString();
        $contents->through(fn (Content $c) => $c->toCardArray());

        return view('contents.index', [
            'contents' => $contents,
            'types' => Content::typeLabels(),
            'themes' => Content::themeLabels(),
            'categories' => Content::categoryLabels(),
            'filters' => $request->only(['type', 'categorie', 'theme', 'access', 'q']),
            'total' => $contents->total(),
        ]);
    }

    public function show(string $slug): View
    {
        $content = Content::published()->with(['author', 'translations'])->where('slug', $slug)->firstOrFail();

        $related = Content::published()
            ->with('translations')
            ->where('id', '!=', $content->id)
            ->when($content->category, fn ($q) => $q->where('category', $content->category))
            ->latest('published_at')
            ->take(4)
            ->get()
            ->map(fn (Content $c) => $c->toCardArray());

        return view('contents.show', [
            'content' => $content,
            'related' => $related,
            'hasAccess' => $content->userHasAccess(auth()->user()),
        ]);
    }
}
