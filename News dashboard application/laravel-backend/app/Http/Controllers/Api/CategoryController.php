<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount(['articles as article_count' => function ($query) {
                $query->whereNotNull('published_at');
            }])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function show(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'error' => 'Category not found',
                'message' => 'Category not found',
            ], 404);
        }

        $articles = Article::query()
            ->with(['user:id,name,email', 'category:id,name,slug'])
            ->where('category_id', $id)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get()
            ->map(function (Article $article) {
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
                    'category_name' => $article->category?->name,
                    'category_slug' => $article->category?->slug,
                    'user_name' => $article->user?->name,
                ];
            })
            ->values();

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'articles' => $articles,
        ]);
    }
}
