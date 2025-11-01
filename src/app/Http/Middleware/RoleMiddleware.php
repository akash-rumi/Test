<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // Variable number of allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized. Authentication required.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. Check if the user's role is in the list of allowed roles
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required role (' . implode(', ', $roles) . ').'
            ], Response::HTTP_FORBIDDEN); // 403 Forbidden
        }

        return $next($request);
    }
}