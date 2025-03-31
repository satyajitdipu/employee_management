<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Security\Encryption;

class OAuthClient extends Model
{
    use HasFactory;
    protected $table = 'oauth_clients';
    protected $guarded = [];
    protected $plainSecret;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string) Str::orderedUuid();
        });
        static::created(function ($oauthClient) {
            $oauthClient->update([
                'client_secret' => $oauthClient->generateClientSecret(),
                'allowed_grant_types' => 'authorization_code',

            ]);
        });
    }

    public static function isConfidential()
    {
        return false;
    }
    public function generateClientSecret()
    {
        return Str::random(40);
    }
    public function getPlainSecretAttribute()
    {
        return $this->plainSecret;
    }

    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = password_hash($value, PASSWORD_BCRYPT);
    }

    public function confidential()
    {
        return !empty($this->secret);
    }
    public function getKeyType()
    {
        return 'string';
    }
    public function getIncrementing()
    {
        return false;
    }
}
