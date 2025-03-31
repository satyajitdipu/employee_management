<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode()
    {
        return new AuthCode;
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        \App\Models\AuthCode::create([
            'code' => $authCodeEntity->getIdentifier(),
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
            'scopes' => $authCodeEntity->getScopes(),
            'revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }

    public function revokeAuthCode($codeId)
    {
        \App\Models\AuthCode::where('code', $codeId)->update(['revoked' => true]);
    }

    public function isAuthCodeRevoked($codeId)
    {
        return \App\Models\AuthCode::where('code', $codeId)->where('revoked', true)->exists();
    }
}
