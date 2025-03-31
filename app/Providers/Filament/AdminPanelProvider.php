<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AuthLogin;
use App\Http\Middleware\PreventNonManagerLogin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Spatie\Permission\Traits\HasRoles;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminPanelProvider extends PanelProvider
{
    use HasRoles;
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('dashboard')
            ->default()
            ->login(AuthLogin::class)
            ->favicon('/assets/images/CranberryCookieLogo.png')
            ->plugins([
                FilamentSocialitePlugin::make()
                    ->setProviders([
                        'google' => [
                            'label' => 'Login using HyScaler Account',
                            // 'icon' => 'fab-google',
                            'iconColor' => 'white',
                            'iconBackgroundColor' => 'success-600',
                            'color' => 'gray-600',
                            // 'backgroundColor' => 'primary',
                        ],
                    ])
                    ->setRegistrationEnabled(true)
                    ->setDomainAllowList(['nettantra.com', 'nettantra.net', 'hyscaler.com', 'testing.com', 'example.com'])
                    ->setRegistrationEnabled(fn (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) => (bool) $user)
                    ->setUserModelClass(\App\Models\User::class)
                    ->setSocialiteUserModelClass(SocialiteUser::class)
            ])
               
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => "#D06348",
            ])
            ->font('Nunito')
            ->darkMode(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->viteTheme('resources/css/filament/app/theme.scss')
            ->authMiddleware([
                Authenticate::class,
                PreventNonManagerLogin::class
            ])
            ->viteTheme('resources/css/filament/app/theme.scss');
    }
}
