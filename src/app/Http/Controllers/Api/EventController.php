<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\EventResource; // Recommended for consistent output
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    /**
     * GET /api/events (Public: pagination, search, filter)
     */
    public function index(Request $request)
    {
        // Create a unique cache key based on all request parameters
        $cacheKey = 'events_list_' . sha1(json_encode($request->query()));
        
        $perPage = $request->get('per_page', 10);
        $ttl = 60 * 5; // Cache time-to-live: 5 minutes

        // Retrieve from cache or execute query
        $events = Cache::remember($cacheKey, $ttl, function () use ($request, $perPage) {
            $query = Event::query();

            // Apply filtering/searching logic (from Section 4)
            if ($request->has('search')) {
                $query->searchByTitle($request->input('search'));
            }
            if ($request->has('date')) {
                $query->filterByDate($request->input('date'));
            }
            if ($request->has('location')) {
                $query->where('location', $request->input('location'));
            }

            // Return the paginated collection
            return $query->paginate($perPage);
        });

        // The paginator object is now retrieved from the cache (or generated and cached)
        return EventResource::collection($events);
    }

    /**
     * GET /api/events/{id} (Public: with tickets)
     */
    public function show(Event $event)
    {
        // Eager load tickets relationship
        $event->load('tickets');

        return new EventResource($event);
    }

    /**
     * POST /api/events (Organizer/Admin Only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:now',
            'location' => 'required|string|max:255',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'location' => $request->location,
            'created_by' => $request->user()->id, // Set the organizer ID
        ]);

        return response()->json([
            'message' => 'Event created successfully.',
            'data' => new EventResource($event)
        ], 201);
    }

    /**
     * PUT /api/events/{id} (Organizer/Admin Only)
     */
    public function update(Request $request, Event $event)
    {
        // Authorization: Ensure organizer only updates their own events
        if ($request->user()->role === 'organizer' && $event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only update your own events.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date|after:now',
            'location' => 'sometimes|string|max:255',
        ]);

        $event->update($request->only('title', 'description', 'date', 'location'));

        return response()->json([
            'message' => 'Event updated successfully.',
            'data' => new EventResource($event)
        ], 200);
    }

    /**
     * DELETE /api/events/{id} (Organizer/Admin Only)
     */
    public function destroy(Request $request, Event $event)
    {
        // Authorization: Ensure organizer only deletes their own events
        if ($request->user()->role === 'organizer' && $event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden. You can only delete your own events.'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.'], 200);
    }
}