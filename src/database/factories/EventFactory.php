<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        // Use a random Organizer ID if not explicitly provided during seeding
        $organizerId = User::where('role', 'organizer')->inRandomOrder()->first()?->id ?? User::factory()->create(['role' => 'organizer'])->id;

        return [
            'title' => $this->faker->unique()->sentence(3) . ' Concert',
            'description' => $this->faker->paragraph(3),
            'date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'location' => $this->faker->city() . ', ' . $this->faker->stateAbbr(),
            'created_by' => $organizerId, // Foreign key linking to an Organizer User
        ];
    }
}