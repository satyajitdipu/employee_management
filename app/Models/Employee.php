<?php

namespace App\Models;

use App\Support\Webhook;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;

class Employee extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use RevisionableTrait;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'employee_code',
        'user_id',
        'manager_id',
        'department_id',
        'designation_id',
        'first_name',
        'middle_name',
        'last_name',
        'head_to_face_ratio',
        'gender',
        'date_of_birth',
        'status',
        'birthday',
        'blood_group', // ['A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−']
        'nationality',
        'country_of_birth',
        'marital_status',

        'field',
        'employee_type_id',
    ];
    protected $casts = [
        'field' => 'array',
    ];

    public  static $jsonAttributes;

    public function addFillableAttribute(array $attributes)
    {
        $this->jsonAttributes = json_encode($attributes);
    }

    public static function boot()
    {
        parent::boot();
    }

    public function getIdAliasAttribute()
    {
        return $this->id;
    }

    public function getYearsOfExperienceAttribute()
    {
        // Ensure that date_of_joining is set for this employee
        if (!$this->date_of_joining) {
            return null;  // or return 0, depending on your requirements
        }

        $joinDate = Carbon::parse($this->date_of_joining);
        $now = Carbon::now();

        $years = $now->diffInYears($joinDate);
        $months = $joinDate->addYears($years)->diffInMonths($now);

        // Convert the years and months to a decimal format
        $yearsOfExperience = $years + ($months / 12);

        return round($yearsOfExperience, 2);
    }

    public static function import_data($employees_data)
    {
        $employees_table_cols = Schema::getColumnListing(app(Employee::class)->getTable());
        $usable_employees_data = [];
        foreach ($employees_data as $employee_data) {
            if (!isset($employee_data['employee_code']) || empty($employee_data['employee_code'])) {
                continue;
            }
            $employee_code = $employee_data['employee_code'];
            $existing_employee = Employee::where('employee_code', $employee_code);
            $emp_data = array_filter($employee_data, function ($v, $k) use ($employees_table_cols) {
                return in_array($k, $employees_table_cols) !== FALSE;
            }, ARRAY_FILTER_USE_BOTH);
            $first_name = $emp_data['first_name'];
            $last_name = (isset($emp_data['middle_name']) && !empty($emp_data['middle_name'])) ? " {$emp_data['middle_name']} {$emp_data['last_name']}" : $emp_data['last_name'];
            $existing_user_id = User::where('email', $emp_data['work_email'])->pluck('id')->first();
            $emp_data['user_id'] = !empty($existing_user_id) ? $existing_user_id : User::factory()->create([
                'name' => "{$first_name} {$last_name}",
                'email' => $emp_data['work_email'],
                'password' => Hash::make(md5(uniqid()))
            ])->assignRole('employee')->id;
            $emp_data['department_id'] = Department::where('name', $employee_data['department'])->pluck('id')->first();
            $emp_data['designation_id'] = Designation::where('name', $employee_data['designation'])->pluck('id')->first();
            $usable_employees_data[$employee_data['employee_code']] = $emp_data;
            $usable_employees_data[$employee_data['employee_code']]['_operation'] = "ignore";
            if ($existing_employee->count() < 1) {
                $usable_employees_data[$employee_data['employee_code']]['_operation'] = "create";
            } elseif ($existing_employee->first()->updated_at->lt($employee_data['_updated_at'])) {
                $usable_employees_data[$employee_data['employee_code']]['_operation'] = "update";
            }
        }
        $status = [];
        foreach ($usable_employees_data as $employee_code => $usable_employee_data) {
            $status[$employee_code] = "ignored";
            $employee_data = array_filter($usable_employee_data, function ($v, $k) {
                return $k[0] != "_";
            }, ARRAY_FILTER_USE_BOTH);
            if ($usable_employee_data['_operation'] == "create") {
                $status[$employee_code] = "created";
                Employee::create($employee_data);
            } elseif ($usable_employee_data['_operation'] == "update") {
                $status[$employee_code] = "updated";
                Employee::where('employee_code', $employee_code)->update($employee_data);
            }
        }
        foreach ($employees_data as $employee_data) {
            if (!isset($employee_data['employee_code']) || empty($employee_data['employee_code'])) {
                continue;
            }
            $employee_code = $employee_data['employee_code'];
            $manager_employee_code = isset($employee_data['manager_employee_code']) && !empty($employee_data['manager_employee_code']) ? $employee_data['manager_employee_code'] : null;
            if (!empty($manager_employee_code)) {
                $manager_id = Employee::where('employee_code', $manager_employee_code)->pluck('id')->first();
                Employee::where('employee_code', $employee_code)->update(['manager_id' => $manager_id]);
                $status[$employee_code] .= " / manager updated";
            }
        }
        return $status;
    }

    /**
     * This employee has many employees who have this employee as their manager.
     *
     * @return A collection of Employee objects.
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * The manager() function returns the Employee model that is related to the current Employee model
     *
     * @return The manager of the employee.
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * > This function returns the designation of the user
     *
     * @return The designation for the employee.
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * > This function returns the user that owns the post
     *
     * @return The user that created the question.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * > This function returns the department that owns the post
     *
     * @return The department that created the question.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class);
    }


    /**
     * > This function returns the attendances of the employee
     *
     * @return The attendances for the employee.
     */
    // public function attendances()
    // {
    //     return $this->hasMany(Attendance::class);
    // }


    /**
     * > This function returns the leaveRequests of the employee
     *
     * @return The leaveRequests for the employee.
     */


    /**
     * > This function returns the leaveBalances of the employee
     *
     * @return The leaveBalances for the employee.
     */

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->performOnCollections('employee_photo')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
        $this
            ->addMediaConversion('large-preview')
            ->performOnCollections('passport_photo')
            ->fit(Fit::Contain, 300, 300)
            ->keepOriginalImageFormat()
            ->nonQueued();
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('passport_photo')
            ->fit(Fit::Contain, 300, 300)
            ->keepOriginalImageFormat()
            ->nonQueued();
    }
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('employee_photo');
        $this
            ->addMediaCollection('passport_photo')
            ->onlyKeepLatest(1);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->isDirty()) {
                Webhook::sendWebhookRequests($model);
            }
            $model->upsert_employee_type_data($model);
        });
    }

    public function upsert_employee_type_data($model)
    {
        $TableName = "employee_type_{$model->employee_type_id}";

        if (Schema::hasTable($TableName)) {
            $emp_details = Employee::where('id', $model->id)->get();

            $data = [];

            if (DB::table($TableName)->where('employee_id', $model->id)->exists()) {
                DB::table($TableName)->where('employee_id', $model->id)->update($model->field);
            } else {
                foreach ($emp_details as $emp_detail) {
                    $emp_column = [];

                    if (!empty($emp_detail->field)) {
                        $emp_column['employee_id'] = $emp_detail->id;
                        $emp_column['employee_type_id'] = $emp_detail->employee_type_id;

                        foreach ($emp_detail->field as $key => $value) {
                            $emp_column[$key] = $value ?? null;
                        }

                        $data[] = $emp_column;
                    }
                }

                DB::table($TableName)->insert($data);
            }
        }
    }
}
