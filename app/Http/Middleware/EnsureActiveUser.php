<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureActiveUser {
    public function handle(Request $request, Closure $next) {
        if ($request->user() && $request->user()->estado !== 'activo') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403);
        }
        return $next($request);
    }
}
