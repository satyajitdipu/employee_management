<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\EmployeeType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DynamicTableInsertion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $employee_type_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->employee_type_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emp_type = EmployeeType::find($this->employee_type_id);

        $emp_type_schema = "employee_type_{$emp_type->id}";

        $employees = Employee::where('employee_type_id', $emp_type->id)->get();

        $data = [];


        foreach ($employees as $employee) {
            $emp_column = [];

            if (!empty($employee->field)) {
                $emp_column['employee_id'] = $employee->id;
                $emp_column['employee_type_id'] = $employee->employee_type_id;


                foreach ($employee->field as $key => $value) {
                    $emp_column[$key] = $value ?? null;
                }

                $data[] = $emp_column;
            }
        }

        DB::table($emp_type_schema)->insert($data);
    }
}
