<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BookingResource;

class BookingController extends Controller
{
    /**
     * POST /api/tickets/{id}/bookings (Customer Only)
     * Requires 'double.booking' middleware from Section 4
     */
    public function store(Request $request, $ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $requestedQuantity = $request->quantity;

        // Check availability
        $availableQuantity = $ticket->quantity - $ticket->bookings()->whereIn('status', ['pending', 'confirmed'])->sum('quantity');

        if ($requestedQuantity > $availableQuantity) {
            return response()->json(['message' => "Requested quantity exceeds available tickets ($availableQuantity available)."], 409);
        }

        // Use transaction for consistency
        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'ticket_id' => $ticket->id,
                'quantity' => $requestedQuantity,
                'status' => 'pending', // Default status before payment
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully. Awaiting payment.',
                'data' => new BookingResource($booking)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Could not create booking.'], 500);
        }
    }

    /**
     * GET /api/bookings (Customer's bookings)
     */
    public function index(Request $request)
    {
        // Customers only see their own bookings
        $bookings = $request->user()
                            ->bookings()
                            ->with(['ticket', 'payment'])
                            ->latest()
                            ->paginate($request->get('per_page', 10));

        return BookingResource::collection($bookings);
    }

    /**
     * PUT /api/bookings/{id}/cancel (Customer Only)
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Authorization: Customer can only cancel their own bookings
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only cancel your own bookings.'], 403);
        }

        // Only cancel if pending or confirmed
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 400);
        }

        $booking->update(['status' => 'cancelled']);

        // Optional: Trigger refund process if payment was successful (Section 5/Payment Logic)

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'data' => new BookingResource($booking)
        ], 200);
    }
}