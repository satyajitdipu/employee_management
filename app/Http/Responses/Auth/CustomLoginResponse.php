<?php

namespace App\Http\Responses\Auth;

use App\Models\OAuthClient;
use App\Support\Encoders;
use App\Support\Str;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $email = auth()->user()?->email ?? '';
        $routeUrl =  request()->getRequestUri();

        if ($routeUrl === "/livewire/update") {
            $routeUrl = request()->header('referer');
            $client_id = Str::getValueFromRequest($routeUrl, 'client_id');
            $oAuthClient = OAuthClient::where('id', $client_id)->first();
            $url = $oAuthClient?->redirect_uri ?? '';
            $secret = $oAuthClient?->client_secret;
        } else {
            $client_id = Str::getValueFromRequest($routeUrl, 'client_id');
            $oAuthClient = OAuthClient::where('id', $client_id)->first();
            $url = $oAuthClient?->redirect_uri ?? '';
            $secret = $oAuthClient?->client_secret;
        }
        $encryptEmail = Encoders::encrypt_request($email, $secret);
        return redirect($url . "/login?token=" . $encryptEmail);
    }
}
