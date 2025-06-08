<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session('user') || strtolower(session('user')['role']) !== 'admin') {
            Log::warning('Unauthorized access attempt to admin route', [
                'user' => session('user'),
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Unauthorized. Admin access required.');
        }

        return $next($request);
    } 
} 