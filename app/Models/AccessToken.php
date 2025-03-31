<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $table = 'access_tokens';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'client_id', 'access_token','scopes', 'expires_at', 'revoked',
    ];

    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'boolean',
    ];

    public $timestamps = false;
}