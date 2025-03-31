<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\OAuthClient;
use Illuminate\Http\Request;
use League\OAuth2\Server\CryptKey;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;


class OAuthController extends Controller
{

    protected $authorizationServer;

    public function __construct(AuthorizationServer $authorizationServer)
    {
        $this->authorizationServer = $authorizationServer;

        $userRepository = app()->make(UserRepositoryInterface::class);
        $clientRepository = app()->make(\League\OAuth2\Server\Repositories\ClientRepositoryInterface::class);
        $accessTokenRepository = app()->make(\League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::class);
        $refreshTokenRepository = app()->make(\League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::class);


        $passwordGrant = new PasswordGrant($userRepository, $refreshTokenRepository);
        $passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // 1 month

        $this->authorizationServer->enableGrantType(
            $passwordGrant,
            new \DateInterval('PT1H') // Access token TTL
        );

        $refreshTokenGrant = new RefreshTokenGrant($refreshTokenRepository);
        $this->authorizationServer->enableGrantType(
            $refreshTokenGrant,
            new \DateInterval('P1M') // Refresh token TTL
        );
    }

    public function handleAuthorization(Request $request)
    {

        $clientId = $request->query('client_id');
        $redirectUri = $request->query('redirect_uri');
        $scope = $request->query('scope');
        $state = $request->query('state');
        $client = OAuthClient::where('id', $clientId)->first();
        if (!$client) {
            return view('auth.unauthorized', ['reason' => 'Invalid client']);
        }
        if ($client->redirect_uri != $redirectUri) {
            return view('auth.unauthorized', ['reason' => 'Redirect URI Mismatch']);
        }

        $clientDetails = OAuthClient::where('id', $request->client_id)->first();
        $redirect_uri = $clientDetails->redirect_uri;

        return view('auth.authorize', [
            'client_id' => $clientId,
            'redirect_uri' => $redirect_uri,
            'state' => $state
        ],);
    }
    public function allowAuthorization(Request $request)
    {
        $credentials = ['email' => $request->username, 'password' => $request->password];
        if (Auth::attempt($credentials)) {
            return view('auth.allow-auth', [
                'response_type' => 'code',
                'client_id' => $request->client_id,
                'redirect_uri' => $request->redirect_uri,
                'scope' => '',
                'state' => $request->state,
            ]);
        } else {
            return view('auth.auth-failure', ['reason' => 'Invalid user name or Password']);
        }
    }

    public function handleAuthorizationLogin(Request $request, ServerRequestInterface $psrRequest, ResponseInterface $response)
    {

        $authenticatedUser = Auth::user();
        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);
            $authRequest->setUser(new \App\Repositories\User($authenticatedUser->getAuthIdentifier()));
            $authRequest->setAuthorizationApproved(true);
            return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }


    public function generateToken(ServerRequestInterface $psrRequest, ResponseInterface $response)
    {
        try {
            return $this->authorizationServer->respondToAccessTokenRequest($psrRequest, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    public function getUserInfo(ServerRequestInterface $psrRequest, ResponseInterface $response)
    {
        $accessTokenRepository = app()->make(\League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::class);
        $publicKey = config('app.public_key');

        try {
            $bearerTokenValidator = new BearerTokenValidator($accessTokenRepository);
            $bearerTokenValidator->setPublicKey(new CryptKey($publicKey));
            $accessToken = $bearerTokenValidator->validateAuthorization($psrRequest);
            $userId = $accessToken->getAttribute('oauth_user_id');
            $user = User::find($userId);

            return ['sub' => $user->sub, 'nickname' => $user->nickname, 'given_name' => $user->name, 'email' => $user->email, 'avatar' => null];
        } catch (OAuthServerException $e) {
            return response()->json(['error' => 'Invalid access token', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error', 'message' => $e->getMessage()], 500);
        }
    }
}
