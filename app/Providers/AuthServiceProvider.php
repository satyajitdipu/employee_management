<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Repositories\ClientRepository;
use App\Repositories\ScopeRepository;
use App\Repositories\AccessTokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use App\Repositories\AuthCodeRepository;
use App\Repositories\RefreshTokenRepository;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use App\Repositories\UserRepository;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\CryptKey;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
        'Spatie\Permission\Models\Permission' => 'App\Policies\PermissionPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */


    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(AccessTokenRepositoryInterface::class, AccessTokenRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
    }

    public function boot()
    {
        $this->registerPolicies();

        $this->app->singleton(AuthorizationServer::class, function ($app) {
            // Resolve dependencies through the service container
            $clientRepository = $app->make(ClientRepository::class);
            $scopeRepository = $app->make(ScopeRepository::class);
            $accessTokenRepository = $app->make(AccessTokenRepository::class);
            $authCodeRepository = $app->make(AuthCodeRepository::class);
            $refreshTokenRepository = $app->make(RefreshTokenRepository::class);
            $userRepository = $app->make(UserRepository::class);

            $privateKey = config('app.private_key');
            $encryptionKey = config('app.secret_key');

            $server = new AuthorizationServer(
                $clientRepository,
                $accessTokenRepository,
                $scopeRepository,
                $privateKey,
                $encryptionKey
            );

            $passwordGrant = new PasswordGrant(
                $userRepository,
                $refreshTokenRepository
            );


            $authCodeGrant = new AuthCodeGrant(
                $authCodeRepository,
                $refreshTokenRepository,
                new \DateInterval('PT10H') // Authorization codes will expire after 10 minutes
            );

            $authCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // Refresh tokens will expire after 1 month
            $server->enableGrantType(
                $passwordGrant,
                new \DateInterval('PT1H')
            );
            $server->enableGrantType(
                $authCodeGrant,
                new \DateInterval('PT1H') // Token TTL for Auth Code Grant
            );
            return $server;
        });

        $this->app->singleton(ResourceServer::class, function ($app) {
            $key = str_replace('\\n', "\n", config('app.public_key'));
            return new ResourceServer(
                $app->make(AccessTokenRepository::class),
                new CryptKey($key, null, false)
            );
        });
    }
}
