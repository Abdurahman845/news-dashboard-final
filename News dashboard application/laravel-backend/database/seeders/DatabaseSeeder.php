<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@newsdashboard.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
            ]
        );

        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business and finance'],
            ['name' => 'Science', 'slug' => 'science', 'description' => 'Scientific discoveries'],
            ['name' => 'Health', 'slug' => 'health', 'description' => 'Health and wellness'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports news'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }

        if (Article::count() === 0) {
            $tech = Category::where('slug', 'technology')->first();
            $business = Category::where('slug', 'business')->first();
            $science = Category::where('slug', 'science')->first();

            $articles = [
                [
                    'title' => 'The Future of AI',
                    'slug' => 'the-future-of-ai',
                    'content' => 'Artificial Intelligence is transforming industries...',
                    'excerpt' => 'Exploring AI developments',
                    'category_id' => $tech?->id,
                ],
                [
                    'title' => 'Market Update',
                    'slug' => 'market-update',
                    'content' => 'Stock markets reach new highs...',
                    'excerpt' => 'Financial news update',
                    'category_id' => $business?->id,
                ],
                [
                    'title' => 'Quantum Breakthrough',
                    'slug' => 'quantum-breakthrough',
                    'content' => 'Scientists achieve quantum computing milestone...',
                    'excerpt' => 'Science news',
                    'category_id' => $science?->id,
                ],
            ];

            foreach ($articles as $article) {
                Article::create([
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'content' => $article['content'],
                    'excerpt' => $article['excerpt'] ?? '',
                    'image_url' => null,
                    'category_id' => $article['category_id'],
                    'user_id' => $admin->id,
                    'published_at' => Carbon::now(),
                ]);
            }
        }
    }
}
