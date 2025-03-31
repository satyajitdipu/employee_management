<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use DutchCodingCompany\FilamentSocialite\Facades\FilamentSocialite as FilamentSocialiteFacade;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use DutchCodingCompany\FilamentSocialite\FilamentSocialite;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        FilamentIcon::register([
            'panels::sidebar.collapse-button' => 'icon-closeMenu',
            'panels::sidebar.expand-button' => 'icon-openMenu',
        ]);

        FilamentSocialiteFacade::setCreateUserCallback(
            function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialite $socialite) {
                $created_user = $socialite->getUserModelClass()::create([
                    'name' => $oauthUser->getName(),
                    'email' => $oauthUser->getEmail(),
                    'password' => Hash::make(md5(uniqid())),
                ])->assignRole('employee');
                $created_user->markEmailAsVerified();
                return $created_user;
            }
        );
    }
}
