<?php

namespace App\Policies;

use App\Models\EmployeeType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeeTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("viewAny employee_types");
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeType $employeeType): bool
    {
        return $user->hasPermissionTo("view employee_types");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo("create employee_types");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeType $employeeType): bool
    {
        return $user->hasPermissionTo("update employee_types");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeType $employeeType): bool
    {
        return $user->hasPermissionTo("delete employee_types");
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeType $employeeType): bool
    {
        return $user->hasPermissionTo("view employee_types");
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeType $employeeType): bool
    {
        return $user->hasPermissionTo("forceDelete employee_types");
    }
}
