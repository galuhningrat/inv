<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLevel
{
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $userPermissions = config("permissions.roles.{$user->level}", []);

        foreach ($modules as $module) {
            if (in_array($module, $userPermissions)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
