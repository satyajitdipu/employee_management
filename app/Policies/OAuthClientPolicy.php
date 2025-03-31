<?php

namespace App\Policies;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OAuthClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("viewAny oauth_clients");
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OAuthClient $oAuthClient): bool
    {
        return $user->hasPermissionTo("view oauth_clients");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo("create oauth_clients");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OAuthClient $oAuthClient): bool
    {
        return $user->hasPermissionTo("update oauth_clients");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OAuthClient $oAuthClient): bool
    {
        return $user->hasPermissionTo("delete oauth_clients");
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OAuthClient $oAuthClient): bool
    {
        return $user->hasPermissionTo("restore oauth_clients");
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OAuthClient $oAuthClient): bool
    {
        return $user->hasPermissionTo("forceDelete oauth_clients");
    }
}
