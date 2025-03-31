<?php

namespace App\Repositories;

use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier)
    {
        $scope = \App\Models\Scope::where('identifier', $identifier)->first();

        return $scope ? $this->createScopeEntityFromDatabaseModel($scope) : null;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        return array_map(function ($scope) {
            return $this->createScopeEntityFromDatabaseModel($scope);
        }, $scopes);
    }

    protected function createScopeEntityFromDatabaseModel(\App\Models\Scope $scope)
    {
        $entity = new \App\Repositories\Scope($scope->identifier); // Use your custom Scope model

        return $entity;
    }
}
