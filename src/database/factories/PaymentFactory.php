<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        // Use a random Booking ID if not explicitly provided during seeding
        $booking = Booking::inRandomOrder()->first() ?? Booking::factory()->create();

        $status = $this->faker->randomElement(['success', 'failed', 'refunded']);
        $transactionId = $status === 'success' ? Str::random(16) : null;

        return [
            'booking_id' => $booking->id,
            // Calculate a plausible amount based on ticket price and quantity if booking existed
            'amount' => $booking->ticket->price * $booking->quantity, 
            'status' => $status,
            'transaction_id' => $transactionId,
        ];
    }
}