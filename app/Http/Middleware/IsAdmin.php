<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login atau role-nya bukan admin, tolak aksesnya!
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Admin.'], 403);
        }

        return $next($request);
    }
}