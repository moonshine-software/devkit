<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Category;
use App\Models\Deployment;
use App\Models\Environment;
use App\Models\Mechanic;
use App\Models\Owner;
use App\Models\Post;
use App\Models\Project;
use App\Models\Shop;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use MoonShine\Laravel\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(50)->create();

        MoonshineUser::factory()->create([
            'name' => 'Admin',
            'email' => 'dev@getmoonshine.app',
            'password' => bcrypt(12345),
        ]);

        Category::factory(20)->create();
        Tag::factory(20)->create();

        Shop::factory(20)->create();
        Mechanic::factory(20)->create();
        Car::factory(20)->create();
        Owner::factory(20)->create();

        Project::factory(20)->create();
        Environment::factory(20)->create();
        Deployment::factory(20)->create();

        Post::factory(50)->create();
    }
}
