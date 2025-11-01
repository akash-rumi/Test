<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Resources\PaymentResource;
use App\Notifications\BookingConfirmed;

class PaymentController extends Controller
{
    /**
     * POST /api/bookings/{id}/payment (Mock payment)
     * We'll inject and use the service here, assuming it exists.
     */
    // public function process(Booking $booking, PaymentService $paymentService) 
    public function process(Booking $booking)
    {
        // Authorization check (Customer owns booking, or Admin/Organizer for testing)
        if (auth()->user()->role === 'customer' && $booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        
        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Payment already processed or booking is cancelled.'], 400);
        }

        // 1. Calculate amount
        $amount = $booking->ticket->price * $booking->quantity;

        // 2. Process payment using the Service
        $result = $paymentService->processPayment($booking->id, $amount);
        $paymentStatus = $result['status'];

        // 3. Create Payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'status' => $paymentStatus,
            'transaction_id' => $result['transaction_id'],
        ]);
        
        // 4. Update Booking status and notify
        if ($paymentStatus === 'success') {
            $booking->update(['status' => 'confirmed']);
            $booking->user->notify(new BookingConfirmed($booking)); // Will be integrated in Section 5

        } else {
            $booking->update(['status' => 'pending']); 
        }

        return response()->json([
            'message' => $result['message'],
            'booking_status' => $booking->status,
            'payment' => new PaymentResource($payment) // Assuming you created PaymentResource
        ], $paymentStatus === 'success' ? 200 : 402);
    }

    /**
     * GET /api/payments/{id}
     */
    public function show(Payment $payment)
    {
        // Ensure only related users (Customer/Admin/Organizer of event) can view
        $user = auth()->user();
        $booking = $payment->booking;
        $eventOrganizerId = $booking->ticket->event->created_by;
        
        if ($user->role === 'customer' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        
        if ($user->role === 'organizer' && $eventOrganizerId !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Admin can view all

        return new PaymentResource($payment);
    }
}