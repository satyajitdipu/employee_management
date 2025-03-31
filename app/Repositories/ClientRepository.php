<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use App\Models\OAuthClient;

class ClientRepository implements ClientRepositoryInterface
{
    use ClientTrait, EntityTrait;
    public function getClientEntity($clientIdentifier)
    {
        $clientEntity = OAuthClient::where('id', $clientIdentifier)->first();
        if (!$clientEntity) {
            return;
        }

        $client = new Client();
        $client->setIdentifier($clientIdentifier);
        $client->setName($clientEntity->name);
        $client->setRedirectUri($clientEntity->redirect_uri);
        $client->setConfidential();
        return $client;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = \App\Models\OAuthClient::where('id', $clientIdentifier)->first();

        if (!$client) {
            return false;
        }
        if ($client->isConfidential() && $client->client_secret !== $clientSecret) {
            return false;
        }
        $allowedGrantTypes = $client->allowed_grant_types;
        if (!in_array($grantType, explode(',', $allowedGrantTypes))) {
            return false;
        }

        return true;
    }
}
