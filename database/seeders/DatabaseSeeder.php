<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\VoteType;
use App\Models\Post;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $users = User::factory()->count(5)->create()->push($testUser);

        $posts = $users->flatMap(
            fn (User $author) => Post::factory()->for($author)->count(3)->create()
        );

        $this->seedInteractions($posts, $users);

        $posts->random(4)->each(fn (Post $post) => $post->forceFill([
            'status' => PostStatus::Closed,
            'closed_at' => now(),
        ])->save());
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @param  Collection<int, User>  $users
     */
    private function seedInteractions(Collection $posts, Collection $users): void
    {
        $votes = app(VoteService::class);

        foreach ($posts as $post) {
            $candidates = $users->where('id', '!=', $post->user_id);

            foreach ($candidates->random(rand(0, $candidates->count())) as $candidate) {
                if (rand(1, 4) === 1) {
                    $votes->follow($post, $candidate);

                    continue;
                }

                $votes->vote($post, $candidate, fake()->randomElement(VoteType::cases()));
            }
        }
    }
}
