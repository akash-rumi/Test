<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;

// --- User/Auth APIs ---
Route::post('register', [AuthController::class, 'register']); // POST /api/register
Route::post('login', [AuthController::class, 'login']);     // POST /api/login

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']); // POST /api/logout
    Route::get('me', [AuthController::class, 'me']);         // GET /api/me

    // --- Event APIs (Public Read/Admin/Organizer Write) ---
    // Public Read (no middleware here for index/show, but we'll add search/filter in Section 3)
    Route::get('events', [EventController::class, 'index']); // GET /api/events 
    Route::get('events/{id}', [EventController::class, 'show']); // GET /api/events/{id}

    // Write access controlled by 'admin' OR 'organizer' role
    Route::middleware('role:admin,organizer')->group(function () {
        Route::post('events', [EventController::class, 'store']);       // POST /api/events
        Route::put('events/{event}', [EventController::class, 'update']); // PUT /api/events/{id}
        Route::delete('events/{event}', [EventController::class, 'destroy']); // DELETE /api/events/{id}
    });

    // --- Ticket APIs (Organizer Only) ---
    Route::middleware('role:admin,organizer')->group(function () {
        Route::post('events/{event_id}/tickets', [TicketController::class, 'store']); // POST /api/events/{event_id}/tickets
        Route::put('tickets/{ticket}', [TicketController::class, 'update']);       // PUT /api/tickets/{id}
        Route::delete('tickets/{ticket}', [TicketController::class, 'destroy']);   // DELETE /api/tickets/{id}
    });

    // --- Booking APIs (Customer Only) ---
    Route::middleware('role:customer')->group(function () {
        Route::post('tickets/{ticket_id}/bookings', [BookingController::class, 'store'])->middleware('double.booking');
        Route::get('bookings', [BookingController::class, 'index']); // GET /api/bookings
        Route::put('bookings/{booking}/cancel', [BookingController::class, 'cancel']); // PUT /api/bookings/{id}/cancel
    });
    
    // Payment API will be handled under bookings or separately
    // We will finish the remaining Section 3 APIs later.
});