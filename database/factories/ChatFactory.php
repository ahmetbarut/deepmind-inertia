<?php

namespace Database\Factories;

use App\Models\ChatItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'user_id' => \App\Models\User::first()->id,
        ];
    }

    /**
     * Indicate that the chat is private.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Chat $chat) {
            ChatItem::factory()
                ->count(10)
                ->create([
                    'chat_id' => $chat->id,
                    'user_id' => $chat->user_id,
                ]);
        });
    }
}
