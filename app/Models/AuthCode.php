<?php

// app/Models/AuthCode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    protected $table = 'auth_codes';

    protected $fillable = [
        'id', 'user_id', 'client_id', 'code', 'scopes', 'expires_at', 'revoked', 
    ];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(OAuthClient::class, 'id');
    }
}
