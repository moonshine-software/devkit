<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fixtures for the Playwright suite in e2e/. Names contain a payload that logs E2E-XSS if it is ever rendered as HTML.
 */
class E2eSeeder extends Seeder
{
    private const PAYLOAD = '<img src=x onerror="console.log(\'E2E-XSS\')">';

    public function run(): void
    {
        $user = User::query()->orderBy('id')->firstOrFail();
        $user->update([
            'name' => 'E2E editable user',
            'email' => 'e2e-user@example.test',
        ]);

        // Keep the filter fixture among the first 50 options and give it exactly one post.
        $otherUser = User::query()->whereKeyNot($user->getKey())->firstOrFail();
        Post::query()
            ->where('user_id', $user->getKey())
            ->update(['user_id' => $otherUser->getKey()]);

        $post = Post::query()->orderBy('id')->firstOrFail();
        $post->update([
            'name' => 'First post',
            'slug' => 'first-post',
            'user_id' => $user->getKey(),
        ]);

        $attached = Category::query()->create(['name' => 'E2E attached '.self::PAYLOAD]);

        // Not attached, so the async search on the post form can find it.
        Category::query()->create(['name' => 'E2E search '.self::PAYLOAD]);

        $post->categories()->attach($attached);

        Project::query()->orderBy('id')->firstOrFail()->update(['name' => 'E2E project']);
        Tag::query()->create(['name' => 'E2E relation tag']);
    }
}
