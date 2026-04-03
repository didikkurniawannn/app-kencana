<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Activity;

class LogLogoutActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ini request logout
        if ($this->isLogoutRequest($request) && auth()->check()) {
            $user = auth()->user();

            $instansiId = \Filament\Facades\Filament::getTenant()?->id;
            if (!$instansiId && method_exists($user, 'instansi')) {
                $instansiId = $user->instansi()->first()?->id;
            }

            Activity::create([
                'log_name'     => 'auth',
                'description'  => 'logout',
                'event'        => 'logout',
                'causer_type'  => get_class($user),
                'causer_id'    => $user->id,
                'subject_type' => get_class($user),
                'subject_id'   => $user->id,
                'instansi_id'  => $instansiId,
                'properties'   => [
                    'nama'  => $user->name,
                    'email' => $user->email,
                    'peran' => $user->getRoleNames()->toArray(),
                ],
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
        }

        return $next($request);
    }

    protected function isLogoutRequest(Request $request): bool
    {
        return $request->isMethod('POST') && str_contains($request->url(), '/logout');
    }
}
