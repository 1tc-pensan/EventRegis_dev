<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $user = Auth::user();
        
        // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve és admin-e
        if (!$user || !$user->is_admin) {
            abort(403, 'Hozzáférés megtagadva. Csak adminok számára elérhető.');
        }
        
        return $next($request);
    }
}
