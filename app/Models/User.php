<?php

namespace App\Models;

use App\Support\Webhook;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use RevisionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code',
        'nickname',
        'sub',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * This function returns true if the user can access the filament.
     *
     * @return bool A boolean value.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['super-admin', 'hr-manager', 'employee']);
    }
    /**
     * The `employee()` function returns a relationship between the `User` model and the `Employee`
     * model
     *
     * @return A single Employee model instance.
     */

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function getId()
    {
        return $this->id;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->sub = 'u_' . uniqid();
            $user->email_verified_at = now();
        });

        static::saving(function ($model) {
            if (empty($model->password)) {
                unset($model->password); // Remove the password from the model's attributes
            }
        });

        //for webhook sync operation
        static::saved(function ($model) {
            $model->load('roles');
            Webhook::sendWebhookRequests($model);
        });
    }
}
