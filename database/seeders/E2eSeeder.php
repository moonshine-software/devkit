<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Fixtures for the Playwright suite in e2e/. Names contain a payload that logs E2E-XSS if it is ever rendered as HTML.
 */
class E2eSeeder extends Seeder
{
    private const PAYLOAD = '<img src=x onerror="console.log(\'E2E-XSS\')">';

    public function run(): void
    {
        $attached = Category::query()->create(['name' => 'E2E attached ' . self::PAYLOAD]);

        // Not attached, so the async search on the post form can find it.
        Category::query()->create(['name' => 'E2E search ' . self::PAYLOAD]);

        Post::query()->orderBy('id')->firstOrFail()->categories()->attach($attached);
    }
}
