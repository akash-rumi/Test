<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- 1. Specific Users (Roles) ---

        // Create 2 Admin Users
        $adminUsers = User::factory(2)->create([
            'role' => 'admin',
            'password' => Hash::make('password'), 
        ]);

        // Create 3 Organizer Users
        $organizerUsers = User::factory(3)->create([
            'role' => 'organizer', 
            'password' => Hash::make('password'), 
        ]);

        // Create 10 Customer Users
        $customerUsers = User::factory(10)->create([
            'role' => 'customer',
            'password' => Hash::make('password'), 
        ]);

        // --- 2. Events (5 Events) --
        
        // Get organizer IDs to ensure events are created by organizers
        $organizerIds = $organizerUsers->pluck('id');
        
        $events = Event::factory(5)->create([
            'created_by' => function () use ($organizerIds) {
                return $organizerIds->random(); // Assign event to a random organizer
            },
        ]);

        // --- 3. Tickets (15 Tickets - 3 per Event) ---
        $allTickets = collect();
        $ticketTypes = ['VIP', 'Standard', 'Early Bird'];

        foreach ($events as $event) {
            foreach ($ticketTypes as $type) {
                // Create a ticket for the current event
                $ticket = Ticket::factory()->create([
                    'event_id' => $event->id, 
                    'type' => $type, 
                    'quantity' => rand(50, 200), 
                    'price' => $type === 'VIP' ? 100.00 : ($type === 'Standard' ? 50.00 : 25.00),
                ]);
                $allTickets->push($ticket);
            }
        }
        
        // --- 4. Bookings and Payments (20 Bookings) --- 
        
        $customerIds = $customerUsers->pluck('id');

        // Loop to create 20 bookings
        for ($i = 0; $i < 20; $i++) {
            $ticket = $allTickets->random();
            $quantity = rand(1, 5); // Book 1 to 5 tickets

            // Create Booking
            $booking = Booking::create([
                'user_id' => $customerIds->random(), 
                'ticket_id' => $ticket->id, 
                'quantity' => $quantity, 
                // Ensure some are confirmed for testing notifications later
                'status' => $i < 16 ? 'confirmed' : $this->faker->randomElement(['pending', 'cancelled']),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Calculate amount
            $amount = $quantity * $ticket->price;
            $paymentStatus = $booking->status === 'confirmed' ? 'success' : 'failed';

            // Create Payment for the Booking
            Payment::create([
                'booking_id' => $booking->id, 
                'amount' => $amount, 
                'status' => $paymentStatus, 
                'transaction_id' => $paymentStatus === 'success' ? Str::random(16) : null,
            ]);
        }
    }
}