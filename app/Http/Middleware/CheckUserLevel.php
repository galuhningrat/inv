<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLevel
{
    protected $permissions = [
        'Admin' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'users', 'requests', 'reports', 'qrCode'],
        'Sarpras' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'reports', 'qrCode'],
        'Rektor' => ['dashboard', 'reports'],
        'Kaprodi' => ['dashboard', 'requests', 'reports'],
        'Keuangan' => ['dashboard', 'requests', 'reports'],
    ];

    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $userPermissions = $this->permissions[$user->level] ?? [];

        foreach ($modules as $module) {
            if (in_array($module, $userPermissions)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}