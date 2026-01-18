<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
final class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'file_name' => $this->faker->word() . '.jpg',
            'mime_type' => 'image/jpeg',
            'path' => 'media/' . $this->faker->uuid() . '.jpg',
            'disk' => 'public',
            'size' => $this->faker->numberBetween(100, 10000),
            'created_by' => User::factory(),
            'super_admin_id' => User::factory(),
        ];
    }
}
