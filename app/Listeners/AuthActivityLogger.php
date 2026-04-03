<?php

namespace App\Listeners;

use App\Models\Activity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;

class AuthActivityLogger
{
    /**
     * Handle user login event.
     */
    public function handleLogin(Login $event): void
    {
        $this->logAuthEvent($event->user, 'login', 'User berhasil login');
    }

    /**
     * Handle user logout event.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->logAuthEvent($event->user, 'logout', 'User berhasil logout');
        }
    }

    /**
     * Handle failed login attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $request = request();

        Activity::create([
            'log_name'     => 'auth',
            'description'  => 'login_failed',
            'event'        => 'login_failed',
            'causer_type'  => null,
            'causer_id'    => null,
            'subject_type' => null,
            'subject_id'   => null,
            'properties'   => [
                'email'  => $event->credentials['email'] ?? 'unknown',
                'guard'  => $event->guard ?? 'web',
                'reason' => 'Invalid credentials',
            ],
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);
    }

    /**
     * Handle password reset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->logAuthEvent($event->user, 'password_reset', 'User mereset password');
    }

    /**
     * Log an authentication event.
     */
    protected function logAuthEvent($user, string $event, string $description): void
    {
        $request = request();

        // Try to get the instansi_id for multi-tenant
        $instansiId = \Filament\Facades\Filament::getTenant()?->id;
        if (!$instansiId && method_exists($user, 'instansi')) {
            $instansiId = $user->instansi()->first()?->id;
        }

        Activity::create([
            'log_name'     => 'auth',
            'description'  => $event,
            'event'        => $event,
            'causer_type'  => $user ? get_class($user) : null,
            'causer_id'    => $user?->id,
            'subject_type' => $user ? get_class($user) : null,
            'subject_id'   => $user?->id,
            'instansi_id'  => $instansiId,
            'properties'   => [
                'name'  => $user?->name ?? 'Unknown',
                'email' => $user?->email ?? 'Unknown',
                'roles' => $user ? $user->getRoleNames()->toArray() : [],
            ],
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);
    }
}
