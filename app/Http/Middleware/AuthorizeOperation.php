<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class AuthorizeOperation {
    public function handle(Request $request, Closure $next) {
        $role = $request->user()?->rol?->nombre;
        abort_unless(in_array($role, ['administrador','analista','supervisor','auditor'], true), 403);
        $route = $request->route()->getName() ?? '';
        if (str_starts_with($route, 'profile.')) return $next($request);
        if (str_starts_with($route, 'usuarios.')) {
            abort_unless($role === 'administrador', 403);
        } elseif ($route === 'versiones.decidir' || str_starts_with($route, 'decisiones.')) {
            abort_unless($role === 'supervisor', 403);
        } elseif (! $request->isMethodSafe()) {
            $allowed = str_starts_with($route, 'catalogos.') || str_starts_with($route, 'versiones.')
                ? ['administrador'] : ['analista','supervisor'];
            if ($route === 'sensibilidad.simular') $allowed = ['administrador','analista','supervisor','auditor'];
            abort_unless(in_array($role, $allowed, true), 403);
        }
        return $next($request);
    }
}
