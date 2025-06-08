<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session('user') || strtolower(session('user')['role']) !== 'sales') {
            Log::warning('Unauthorized access attempt to sales route', [
                'user' => session('user'),
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Sales access required.'
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Unauthorized. Sales access required.');
        }

        return $next($request);
    }
} 