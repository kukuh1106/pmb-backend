<?php

namespace App\Http\Middleware;

use App\Models\Pendaftar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // For Pendaftar (uses custom model)
        if ($role === 'pendaftar' && $user instanceof Pendaftar) {
            return $next($request);
        }

        // For Admin/Prodi (uses User model with role field)
        if ($user->role === $role) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden - You do not have permission to access this resource',
        ], 403);
    }
}
