<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        \App\Models\RefreshToken::create([
            'refresh_token' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'user_id' => $refreshTokenEntity->getAccessToken()->getUserIdentifier(),
            'expires_at' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }

    public function revokeRefreshToken($tokenId)
    {
        \App\Models\RefreshToken::where('refresh_token', $tokenId)->update(['revoked' => true]);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return \App\Models\RefreshToken::where('refresh_token', $tokenId)->where('revoked', true)->exists();
    }
}
