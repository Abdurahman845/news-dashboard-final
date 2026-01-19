<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()
            ->with(['user:id,name,email', 'category:id,name,slug'])
            ->whereNotNull('published_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }

        $categorySlug = trim((string) $request->query('category', ''));
        if ($categorySlug !== '') {
            $query->whereHas('category', function ($builder) use ($categorySlug) {
                $builder->where('slug', $categorySlug);
            });
        }

        $query->orderByDesc('published_at');

        if ($request->filled('per_page')) {
            $perPage = max(1, (int) $request->input('per_page', 10));
            $page = max(1, (int) $request->input('page', 1));
            $articles = $query->forPage($page, $perPage)->get();
        } else {
            $articles = $query->get();
        }

        return response()->json(
            $articles->map(fn (Article $article) => $this->articlePayload($article))->values()
        );
    }

    public function show(Article $article)
    {
        $article->load(['user:id,name,email', 'category:id,name,slug']);

        return response()->json($this->articlePayload($article));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $user = $request->attributes->get('auth_user');
        $title = (string) $request->input('title');
        $content = (string) $request->input('content');

        $slug = $this->buildUniqueSlug($title);

        $article = Article::create([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $request->input('excerpt', ''),
            'image_url' => $request->input('image_url'),
            'category_id' => $request->integer('category_id'),
            'user_id' => $user->id,
            'published_at' => $request->filled('published_at')
                ? Carbon::parse($request->input('published_at'))
                : Carbon::now(),
        ]);

        $article->load(['user:id,name,email', 'category:id,name,slug']);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $this->articlePayload($article),
        ], 201);
    }

    public function update(Request $request, Article $article)
    {
        $user = $request->attributes->get('auth_user');

        if ($article->user_id !== $user->id) {
            return response()->json([
                'error' => 'You do not have permission to edit this article',
                'message' => 'You do not have permission to edit this article',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'min:3', 'max:255'],
            'content' => ['sometimes', 'string', 'min:10'],
            'excerpt' => ['sometimes', 'nullable', 'string'],
            'image_url' => ['sometimes', 'nullable', 'string'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $data = [];

        if ($request->filled('title')) {
            $title = (string) $request->input('title');
            $data['title'] = $title;
            $data['slug'] = $this->buildUniqueSlug($title, $article->id);
        }

        foreach (['content', 'excerpt', 'image_url'] as $field) {
            if ($request->exists($field)) {
                $data[$field] = $request->input($field);
            }
        }

        if ($request->exists('category_id')) {
            $data['category_id'] = $request->integer('category_id');
        }

        if ($request->exists('published_at')) {
            $data['published_at'] = $request->input('published_at')
                ? Carbon::parse($request->input('published_at'))
                : null;
        }

        if (!empty($data)) {
            $article->update($data);
        }

        $article->load(['user:id,name,email', 'category:id,name,slug']);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $this->articlePayload($article),
        ]);
    }

    public function destroy(Request $request, Article $article)
    {
        $user = $request->attributes->get('auth_user');

        if ($article->user_id !== $user->id) {
            return response()->json([
                'error' => 'You do not have permission to delete this article',
                'message' => 'You do not have permission to delete this article',
            ], 403);
        }

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }

    protected function buildUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Article::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function articlePayload(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'excerpt' => $article->excerpt,
            'image_url' => $article->image_url,
            'category_id' => $article->category_id,
            'user_id' => $article->user_id,
            'published_at' => $article->published_at,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'user_name' => $article->user?->name,
            'category_name' => $article->category?->name,
            'category_slug' => $article->category?->slug,
            'user' => $article->user
                ? [
                    'id' => $article->user->id,
                    'name' => $article->user->name,
                    'email' => $article->user->email,
                ]
                : null,
            'category' => $article->category
                ? [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                ]
                : null,
        ];
    }
}
