<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessToken($userIdentifier, $scopes, $clientEntity);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        \App\Models\AccessToken::create([
            'access_token' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => json_encode($accessTokenEntity->getScopes()),
            'expires_at' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }

    public function revokeAccessToken($tokenId)
    {

        \App\Models\AccessToken::where('access_token', $tokenId)->update(['revoked' => true]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return \App\Models\AccessToken::where('access_token', $tokenId)->where('revoked', true)->exists();
    }
}
