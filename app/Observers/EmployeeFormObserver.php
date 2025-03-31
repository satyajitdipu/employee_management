<?php

namespace App\Observers;

use App\Models\EmployeeForm;
use App\Support\Encoders;

class EmployeeFormObserver
{
    /**
     * Handle the EmployeeForm "creating" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function creating(EmployeeForm $employeeForm): void
    {
        if(!isset($employeeForm->user_id))
            $employeeForm->user()->associate(auth()->user());
    }
    /**
     * Handle the EmployeeForm "created" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function created(EmployeeForm $employeeForm)
    {
        $employeeForm->short_code = Encoders::short_code_from_id($employeeForm->id);
        $employeeForm->save();
    }

    /**
     * Handle the EmployeeForm "updated" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function updated(EmployeeForm $employeeForm)
    {
        //
    }

    /**
     * Handle the EmployeeForm "deleted" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function deleted(EmployeeForm $employeeForm)
    {
        //
    }

    /**
     * Handle the EmployeeForm "restored" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function restored(EmployeeForm $employeeForm)
    {
        //
    }

    /**
     * Handle the EmployeeForm "force deleted" event.
     *
     * @param  \App\Models\EmployeeForm  $employeeForm
     * @return void
     */
    public function forceDeleted(EmployeeForm $employeeForm)
    {
        //
    }
}
