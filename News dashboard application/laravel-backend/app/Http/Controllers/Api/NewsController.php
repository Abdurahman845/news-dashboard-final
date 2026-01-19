<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function autoFetch(Request $request)
    {
        if (!filter_var(config('news.auto_fetch_enabled', env('AUTO_FETCH_ENABLED', true)), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json(['error' => 'Auto fetch is disabled'], 403);
        }

        $apiKey = config('news.api_key', env('NEWS_API_KEY'));
        $apiKey = $apiKey ?: $request->query('api_key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'API key not configured',
                'message' => 'Please configure NEWS_API_KEY in the .env file or provide ?api_key= in the request',
            ], 400);
        }

        $category = $request->query('category', config('news.auto_fetch_category', env('AUTO_FETCH_CATEGORY', 'technology')));
        $count = (int) $request->query('count', config('news.auto_fetch_count', env('AUTO_FETCH_COUNT', 10)));

        return $this->importFromNewsApi($apiKey, $category, $count);
    }

    public function fetch(Request $request)
    {
        $category = $request->input('category', 'technology');
        $count = (int) $request->input('count', 10);
        $apiKey = $request->input('api_key');

        $user = $this->resolveUserFromHeader($request);

        if (!$user && empty($apiKey)) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide a NewsAPI key or log in first',
            ], 401);
        }

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'To fetch real news articles, you need a NewsAPI key. Get one free at https://newsapi.org/register',
                'alternative' => 'You can also use the /api/articles POST endpoint to create articles manually',
            ], 400);
        }

        return $this->importFromNewsApi($apiKey, $category, $count, $user);
    }

    protected function importFromNewsApi(string $apiKey, string $category, int $count, ?User $user = null)
    {
        $user ??= $this->adminUser();
        $count = max(1, min($count, 20));

        $categoryModel = Category::where('slug', $category)->first();
        if (!$categoryModel) {
            return response()->json(['error' => 'Category not found'], 400);
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'X-Api-Key' => $apiKey,
                    'User-Agent' => 'News Dashboard App',
                ])
                ->get('https://newsapi.org/v2/top-headlines', [
                    'category' => $category,
                    'pageSize' => $count,
                    'language' => 'en',
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'CURL error',
                'details' => $e->getMessage(),
            ], 500);
        }

        if ($response->failed()) {
            $error = $response->json();

            return response()->json([
                'error' => 'Failed to fetch news from NewsAPI',
                'http_code' => $response->status(),
                'details' => $error['message'] ?? $response->body(),
            ], 500);
        }

        $payload = $response->json();
        $articles = $payload['articles'] ?? [];

        if (empty($articles)) {
            return response()->json(['error' => 'No articles found'], 404);
        }

        $imported = 0;
        $skipped = 0;

        foreach ($articles as $item) {
            $title = $item['title'] ?? null;
            $content = $item['content'] ?? ($item['description'] ?? null);

            if (!$title || !$content) {
                $skipped++;
                continue;
            }

            if (Article::where('title', $title)->exists()) {
                $skipped++;
                continue;
            }

            $slug = $this->uniqueSlug($title);
            $excerpt = $item['description'] ?? Str::limit(strip_tags($content), 150, '...');

            Article::create([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'image_url' => $item['urlToImage'] ?? null,
                'category_id' => $categoryModel->id,
                'user_id' => $user->id,
                'published_at' => Carbon::now(),
            ]);

            $imported++;
        }

        return response()->json([
            'message' => 'News articles imported successfully',
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($articles),
        ]);
    }

    protected function adminUser(): User
    {
        $email = 'admin@newsdashboard.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => 'Admin User',
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);
    }

    protected function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function resolveUserFromHeader(Request $request): ?User
    {
        $header = $request->header('Authorization') ?? $request->server('HTTP_AUTHORIZATION');

        if (!$header || !Str::startsWith(Str::lower($header), 'bearer ')) {
            return null;
        }

        $token = trim(Str::substr($header, 7));

        if (empty($token)) {
            return null;
        }

        $tokenModel = Token::with('user')->where('token', $token)->first();

        return $tokenModel?->user;
    }
}
