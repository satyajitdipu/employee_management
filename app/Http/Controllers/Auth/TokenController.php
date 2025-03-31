<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;

class TokenController extends Controller
{
    protected $bearerTokenValidator;

    public function __construct(BearerTokenValidator $bearerTokenValidator)
    {
        $this->bearerTokenValidator = $bearerTokenValidator;
    }
    public function userInfo(Request $request, ServerRequestInterface $psrRequest, ResponseInterface $response)
    {
        return [
            'sub' => '1',
            'nickname' => 'Hyscaler',
            'email' => 'admin@hyscaler.com',
        ];
        // try {
        //     return $this->bearerTokenValidator->validateAuthorization($psrRequest);
        // } catch (OAuthServerException $exception) {
        //     return $exception->generateHttpResponse($response);
        // }
    }
}
