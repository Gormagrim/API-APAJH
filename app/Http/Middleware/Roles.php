<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $levelAccess)
    {
        if (auth()->user() == null) {
            return response('Vous devez vous connecter.', 401);
        } else if (auth()->user()->isActive == 0) {
            return response('Vous devez valider votre inscription en cliquant sur le lien présent dans l\'email que nous venons de vous envoyer.', 401);
        } else {
            $test = auth()->user()->usergroup->levelAccess;
            if ($test >= $levelAccess) {
                return $next($request);
            } else {
                return response('Vous n\'êtes pas autorisé', 401);
            }
        }
    }
}
