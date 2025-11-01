<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        // Use a random Event ID if not explicitly provided during seeding
        $eventId = Event::inRandomOrder()->first()?->id ?? Event::factory()->create()->id;

        $types = ['Standard', 'VIP', 'Early Bird'];
        $type = $this->faker->randomElement($types);

        return [
            'event_id' => $eventId,
            'type' => $type,
            'price' => match ($type) {
                'VIP' => $this->faker->randomFloat(2, 80, 150),
                'Early Bird' => $this->faker->randomFloat(2, 20, 40),
                default => $this->faker->randomFloat(2, 40, 80),
            },
            'quantity' => $this->faker->numberBetween(50, 500),
        ];
    }
}