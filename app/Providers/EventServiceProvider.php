<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\AuthActivityLogger;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        $logger = new AuthActivityLogger();

        Event::listen(Login::class, [$logger, 'handleLogin']);
        Event::listen(Logout::class, [$logger, 'handleLogout']);
        Event::listen(Failed::class, [$logger, 'handleFailed']);
        Event::listen(PasswordReset::class, [$logger, 'handlePasswordReset']);
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
