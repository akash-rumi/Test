<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        // Use a random Customer ID if not explicitly provided during seeding
        $userId = User::where('role', 'customer')->inRandomOrder()->first()?->id ?? User::factory()->create(['role' => 'customer'])->id;
        
        // Use a random Ticket ID
        $ticketId = Ticket::inRandomOrder()->first()?->id ?? Ticket::factory()->create()->id;

        return [
            'user_id' => $userId, // Must be a customer
            'ticket_id' => $ticketId,
            'quantity' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}