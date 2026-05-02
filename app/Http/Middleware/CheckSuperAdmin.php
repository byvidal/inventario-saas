<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificamos si el usuario está autenticado y es super_admin
        if (auth()->check() && auth()->user()->role === 'super_admin') {
            return $next($request);
        }

        abort(403, 'Acceso denegado. Solo el dueño del SaaS puede entrar aquí.');
    }
}