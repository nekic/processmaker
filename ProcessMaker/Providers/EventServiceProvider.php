<?php

namespace ProcessMaker\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Register our Events and their Listeners
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Failed' => [
            'ProcessMaker\Listeners\SecurityLogger',
        ],
        'Illuminate\Auth\Events\Login' => [
            'ProcessMaker\Listeners\LoginListener',
            'ProcessMaker\Listeners\SecurityLogger',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'ProcessMaker\Listeners\SecurityLogger',
        ],
        'ProcessMaker\Events\SessionStarted' => [
            'ProcessMaker\Listeners\ActiveUserListener',
        ],
        'Illuminate\Database\Events\MigrationsEnded' => [
            'ProcessMaker\Listeners\UpdateDataLakeViews',
        ],
        'ProcessMaker\Events\AuthClientUpdated' => [
            'ProcessMaker\Listeners\SecurityLogger',
        ],
        'ProcessMaker\Events\AuthClientCreated' => [
            'ProcessMaker\Listeners\SecurityLogger',
        ],
        'ProcessMaker\Events\AuthClientDeleted' => [
            'ProcessMaker\Listeners\SecurityLogger',
        ]
    ];

    /**
     * Register any events for your application.
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
