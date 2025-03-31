<?php

namespace App\Providers;

use App\Models\EmployeeForm;
use App\Observers\EmployeeFormObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent::class => [
            \App\Listeners\GeneratePassportPhoto::class,
            \App\Listeners\GenerateIdentityCard::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    // public function boot()
    // {
    //     EmployeeForm::observe(EmployeeFormObserver::class);
    // }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
