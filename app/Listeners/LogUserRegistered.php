<?php

namespace App\Listeners;

use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Registered;

class LogUserRegistered
{
    public function handle(Registered $event): void
    {
        ActivityLogService::log(
            event: 'register',
            userId: $event->user->id,
        );
    }
}
