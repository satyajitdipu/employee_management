<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OAuthClient;

class OAuthClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OAuthClient::factory()->create([
            'name' => 'My Client',
            'redirect_uri' => 'http://127.0.0.1:8001/oauth/callback/auth0',
            'allowed_grant_types' => 'authorization_code',
        ]);

        OAuthClient::factory()->create([
            'name' => 'Another Client',
            'redirect_uri' => 'http://localhost/another-callback',

        ]);
    }
}
