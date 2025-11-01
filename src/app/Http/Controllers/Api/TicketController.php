<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\TicketResource;

class TicketController extends Controller
{
    /**
     * POST /api/events/{event_id}/tickets (Organizer/Admin Only)
     */
    public function store(Request $request, $event_id)
    {
        $event = Event::findOrFail($event_id);

        // Authorization: Ensure organizer owns the event (RBAC already checks role)
        if ($request->user()->role === 'organizer' && $event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only add tickets to your own events.'], 403);
        }

        $request->validate([
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticket = $event->tickets()->create([
            'type' => $request->type,
            'price' => $request->price,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => new TicketResource($ticket)
        ], 201);
    }

    /**
     * PUT /api/tickets/{id} (Organizer/Admin Only)
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Eager load event to check ownership
        $ticket->load('event');

        // Authorization: Ensure organizer owns the event
        if ($request->user()->role === 'organizer' && $ticket->event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only update tickets for your own events.'], 403);
        }

        $request->validate([
            'type' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0.01',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $ticket->update($request->only('type', 'price', 'quantity'));

        return response()->json([
            'message' => 'Ticket updated successfully.',
            'data' => new TicketResource($ticket)
        ], 200);
    }

    /**
     * DELETE /api/tickets/{id} (Organizer/Admin Only)
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        // Eager load event to check ownership
        $ticket->load('event');

        // Authorization: Ensure organizer owns the event
        if ($request->user()->role === 'organizer' && $ticket->event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only delete tickets for your own events.'], 403);
        }

        // Check if there are any confirmed/pending bookings before deleting
        if ($ticket->bookings()->whereIn('status', ['confirmed', 'pending'])->exists()) {
             return response()->json(['message' => 'Cannot delete ticket. Active bookings exist.'], 409);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully.'], 200);
    }
}