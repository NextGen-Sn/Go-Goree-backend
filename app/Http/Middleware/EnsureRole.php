<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorise la requête uniquement si l'utilisateur authentifié possède l'un des
 * rôles attendus. Usage : ->middleware('role:Admin') ou 'role:Admin,Agent'.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $user?->role?->nom;

        // $role est un RoleEnum (cast sur Role::nom) : on compare sa valeur.
        $roleValeur = $role instanceof RoleEnum ? $role->value : $role;

        if (! $user || $roleValeur === null || ! in_array($roleValeur, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Accès non autorisé pour votre rôle.');
        }

        return $next($request);
    }
}
