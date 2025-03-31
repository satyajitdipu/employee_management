<?php
namespace App\Http\Controllers\Views;

use App\Models\OAuthSource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class OAuthRedirectController
{
    protected $params = null;

    public function getAdditionalParameters(OAuthSource $source)
    {
        return $this->params ?? [];
    }

    public function getCallbackUrl(OAuthSource $source)
    {
        return route('authentik_sources_oauth.oauth-client-callback', ['source_slug' => $source->slug]);
    }

    public function getRedirectUrl(Request $request)
    {
        $slug = $request->input('source_slug', '');
        try {
            $source = OAuthSource::where('slug', $slug)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            abort(404, "Unknown OAuth source '{$slug}'.");
        }
        if (!$source->enabled) {
            abort(404, "source {$slug} is not enabled.");
        }

        $client = $this->getClient($source, ['callback' => $this->getCallbackUrl($source)]);
        $params = $this->getAdditionalParameters($source);
        $params['scope'] = [];
        if (!empty($source->additional_scopes)) {
            if (Str::startsWith($source->additional_scopes, '*')) {
                $params['scope'] = explode(' ', substr($source->additional_scopes, 1));
            } else {
                $params['scope'] = array_merge($params['scope'], explode(' ', $source->additional_scopes));
            }
        }

        return $client->getRedirectUrl($params);
    }

    public function redirectToOAuth(Request $request)
    {
        $redirectUrl = $this->getRedirectUrl($request);
        return new RedirectResponse($redirectUrl);
    }

    public function getClient(OAuthSource $source, $options = [])
    {
        // Assuming OAuthClient is a class used for OAuth handling
        return new OAuthClient($source, $options);
    }
}
