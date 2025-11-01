<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Booking;

class PreventDoubleBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // The ticket_id is passed as a route parameter in the booking route
        $ticketId = $request->route('ticket_id'); 

        // Check for existing pending or confirmed bookings for this user and ticket
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending', 'confirmed']) 
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'message' => 'You already have a pending or confirmed booking for this ticket.'
            ], Response::HTTP_CONFLICT); // 409 Conflict: Request could not be completed due to a conflict
        }

        return $next($request);
    }
}