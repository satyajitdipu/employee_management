<?php

namespace App\Repositories;

use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use App\Models\User;
use App\Entity\UserEntity;


class UserRepository implements UserRepositoryInterface
{
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        // Example: Retrieve a user entity from your application's user database
        $user = User::where('email', $username)->first();

        // Validate user credentials
        if (!$user || !password_verify($password, $user->password)) {
            throw OAuthServerException::invalidCredentials();
        }

        // Return a UserEntityInterface instance
        return new \App\Repositories\User($user->getId());
    }
}
