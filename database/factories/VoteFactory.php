<?php

namespace Database\Factories;

use App\Enums\VoteType;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(VoteType::cases()),
        ];
    }

    public function recommend(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VoteType::Recommend,
        ]);
    }

    public function notRecommend(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VoteType::NotRecommend,
        ]);
    }
}
