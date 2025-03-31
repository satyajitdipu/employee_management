<?php

namespace App\Filament\Pages;

use App\Http\Responses\Auth\CustomLoginResponse;
use App\Models\OAuthClient;
use App\Support\Str;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Login as BasePage;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;

class AuthLogin extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.auth-login';

    // public function mount(): void
    // {
    //     $routeUrl =  request()->getRequestUri();
    //     $client = Str::getValueFromRequest($routeUrl, 'client');
    //     $server_redirect_uri = OAuthClient::where('name', $client)->first()?->server_redirect_uri;
    //     $url = config("app.url") . $server_redirect_uri;
    //     if ($routeUrl === $url) {
    //         app(CustomLoginResponse::class);
    //     }
    // }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->label('')
                    ->placeholder(__('filament::login.fields.email.placeholder'))
                    ->extraAttributes([
                        'class' => 'login-form-btn',
                    ]),
                $this->getPasswordFormComponent()
                    ->label('')
                    ->placeholder(__("filament::login.fields.password.placeholder"))
                    ->extraAttributes([
                        'class' => 'login-form-btn',
                    ]),
                $this->getRememberFormComponent()
                    ->label(__('filament::login.fields.remember.label')),
            ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction()
                ->label(__('filament::login.buttons.submit.label'))
                ->extraAttributes([
                    'class' => 'login-button'
                ]),
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (!$user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }
        session()->regenerate();

        $routeUrl = request()->header('referer');
        $client_id = Str::getValueFromRequest($routeUrl, 'client_id');
        $url = config("app.url") . "dashboard/login?client_id=" . $client_id;

        if ($routeUrl === $url) {
            return app(CustomLoginResponse::class);
        }

        return app(LoginResponse::class);
    }
}
