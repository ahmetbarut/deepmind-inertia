<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatItem>
 */
class ChatItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => \App\Models\Chat::factory(),
            'user_id' => \App\Models\User::first()->id,
            'message' => $this->faker->sentence(),
            'type' => fake()->randomElement(\App\Enums\ChatType::values()),
        ];
    }
}
