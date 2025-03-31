<x-filament-panels::page>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Auth0 Integration Documentation</title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                margin: 20px;
            }

            h1,
            h2,
            h3 {
                color: #333;
            }

            pre {
                background-color: #f4f4f4;
                padding: 10px;
                overflow-x: auto;
            }

            code {
                font-family: 'Courier New', Courier, monospace;
            }
        </style>
    </head>

    <body>
        <h1>Cookie Integration Documentation</h1>

        <h2>Step 1: Install Auth0</h2>
        <p>To integrate Cookie server into your Laravel application, you need to install the required package using Composer:</p>
        <pre><code>composer require socialiteproviders/auth0</code></pre>
        <li>Setup the event service providers.</li>
        <pre><code>
        // Provides/EventServicesProvides.php

        protected $listen = [

            \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Auth0\Auth0ExtendSocialite::class . '@handle',
        ],
        //other event class
    ];
        </code></pre>

        <li>Update the configuration in your Laravel application's <code>config/app.php file:</code></li>

        <pre><code>
        // config/app.php

        'providers' => [
            Laravel\Socialite\SocialiteServiceProvider::class,
            \SocialiteProviders\Manager\ServiceProvider::class,

            // other provides
        ],
            'aliases' => Facade::defaultAliases()->merge([])->toArray(),

        </code></pre>

        <h2>Step 2: Configure Auth0</h2>
        <p>Update the configuration in your Laravel application's <code>config/services.php</code> file:</p>
        <pre>
        <code>
            // config/services.php
            'auth0' => [
                'client_id' => 'YOUR_CLIENT_ID',
                'client_secret' => 'YOUR_CLIENT_SECRET',
                'redirect_uri' => '/oauth/callback/auth0',
                'base_uri' => 'YOUR_BASE_URI',
            ],
            // base_uri cookie.nettantra.com/oauth
        </code>
    </pre>

        <h2>Step 3: Filament Socialite Configuration</h2>
        <p>Adjust the Socialite configuration specific to Auth0 in <code>config/filament-socialite.php</code>:</p>
        <pre>
        <code>
            // config/filament-socialite.php
            'auth0' => [
                'label' => 'Auth0',
                // Add any other configuration specific to Auth0 for Filament here
            ],
        </code>
    </pre>

        <h2>Step 4: Usage</h2>
        <p>With Auth0 configured, you can now use it for user authentication in your Laravel application. Here's an example of how you might use it in your code:</p>
        <pre>
        <code>
            // Example Controller method
            public function redirectToAuth0Provider()
            {
                return Socialite::driver('auth0')->redirect();
            }

            public function handleAuth0Callback()
            {
                $user = Socialite::driver('auth0')->user();

                // Handle the authenticated user as needed
            }
        </code>
    </pre>

        <h2>Additional Resources</h2>
        <ul>
            <li><a href="https://auth0.com/docs/quickstarts/" target="_blank">Auth0 Quickstarts</a></li>
            <li><a href="https://laravel.com/docs/socialite" target="_blank">Laravel Socialite Documentation</a></li>
        </ul>

        <p>Make sure to replace <code>YOUR_CLIENT_ID</code>, <code>YOUR_CLIENT_SECRET</code>, and <code>YOUR_BASE_URI</code> with your actual Auth0 credentials.</p>
    </body>

    </html>


</x-filament-panels::page>