<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Category;
use App\Models\Mechanic;
use App\Models\PolyComment;
use App\Models\Post;
use App\Models\Project;
use App\Models\Shop;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use MoonShine\Laravel\Resources\ModelResource;

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

        $post->comments()->create([
            'user_id' => $user->getKey(),
            'text' => 'E2E child comment',
            'imageable_type' => Post::class,
            'imageable_id' => $post->getKey(),
        ]);
        $post->image()->create(['url' => '/vendor/moonshine/avatar.jpg']);
        PolyComment::query()->firstOrFail()->update([
            'text' => 'E2E morph comment',
            'commentable_type' => Post::class,
            'commentable_id' => $post->getKey(),
        ]);

        $shops = Shop::query()->orderBy('id')->take(2)->get();
        foreach ($shops as $index => $shop) {
            $shop->update(['name' => 'E2E shop '.($index + 1)]);
            Mechanic::query()->whereKey($index + 1)->update([
                'name' => 'E2E mechanic '.($index + 1),
                'shop_id' => $shop->getKey(),
            ]);
        }
        Car::query()->orderBy('id')->firstOrFail()->update([
            'shop_id' => $shops->first()->getKey(),
            'mechanic_id' => 1,
        ]);

        Project::query()->orderBy('id')->firstOrFail()->update(['name' => 'E2E project']);
        Tag::query()->create(['name' => 'E2E relation tag']);

        $resources = moonshine()->getResources()->mapWithKeys(function ($resource): array {
            $item = $resource instanceof ModelResource ? $resource->getModel()->newQuery()->first() : null;

            return [$resource->getUriKey() => [
                'id' => $item?->getKey() ?? 1,
                'title' => $resource->getTitle(),
                'record' => $item?->getRawOriginal('name') ?? $item?->getRawOriginal('text') ?? $item?->getRawOriginal('url'),
                'pages' => $resource->getPages()->mapWithKeys(
                    static fn ($page): array => [$page->getPageType()->value => $page->getUriKey()],
                )->all(),
            ]];
        })->all();
        $pages = moonshine()->getPages()
            ->filter(static fn ($page): bool => $page->getPageType() === null)
            ->mapWithKeys(static fn ($page): array => [$page->getUriKey() => $page::class])
            ->all();

        File::ensureDirectoryExists(base_path('.playwright-mcp'));
        File::put(base_path('.playwright-mcp/e2e-inventory.json'), json_encode([
            'resources' => $resources,
            'pages' => $pages,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
