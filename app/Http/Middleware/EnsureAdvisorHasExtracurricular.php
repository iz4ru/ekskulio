<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdvisorHasExtracurricular
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user =  Auth::user();

        if ($user && $user->role === 'pembina') {
            $hasExtracurricular = $user->extracurricularList()->exists();

            if (!$hasExtracurricular) {
                // Kalau AJAX/API request
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Anda belum diampu ke ekstrakurikuler manapun.'
                    ], 403);
                }

                // Kalau web request — redirect ke halaman khusus
                return redirect()->route('unauthorized.no-extracurricular');
            }
        }

        return $next($request);
    }
}
