<?php

namespace App\Models;

use App\Jobs\DynamicTableInsertion;
use App\Enums\EmployeeFormStatus;
use App\Models\Scopes\EmployeeFormScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\FieldTypeEnums;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class EmployeeType extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'short_code',
        'description',
        'status',
        'fields',
    ];

    protected $casts = [
        'status' => EmployeeFormStatus::class,
        'fields' => 'array',
    ];

    protected $attributes = [
        'status' => EmployeeFormStatus::DRAFT,
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->hasMany(Employee::class);
    }

    protected static function booted()
    {
        static::creating(function ($employeeType) {
            $employeeType->generateShortCode();
        });

        static::saved(function ($model) {
            if (!$model->isDirty('status')) return;

            if ($model->status != EmployeeFormStatus::PUBLISHED()->value) {
                $model->destroy_employee_type_schema();
                return;
            }

            $model->build_employee_type_schema();
            $model->populate_employee_type_schema();
        });
    }

    public function build_employee_type_schema($force_recreate = false)
    {
        $schema = [];

        // first get the field1 jsonarray then from that , get 'field_details' array for table creation

        foreach ($this->fields as $section) {
            foreach ($section['field_details'] as $field) {
                $schema[$field['field_key']] = [
                    'type' => FieldTypeEnums::getSchemaFields($field['field_type']),
                    'label' => $field['field_label'],
                    'required' => $field['field_is_required'],
                ];
            }
        }
        $table_name = "employee_type_{$this->id}";
        if ($force_recreate) {
            Schema::dropIfExists($table_name);
        }
        if (Schema::hasTable($table_name)) {
            return;
        }

        Schema::create($table_name, function (Blueprint $table) use ($schema) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unsignedBigInteger('employee_type_id');
            $table->foreign('employee_type_id')->references('id')->on('employee_types')->onDelete('cascade');
            foreach ($schema as $key => $value) {
                $table->{$value['type']}($key)->nullable(!$value['required']);
            }
            $table->timestamps();
        });
    }
    public function destroy_employee_type_schema()
    {
        $table_name = "employee_type_{$this->id}";
        Schema::dropIfExists($table_name);
    }

    public function populate_employee_type_schema()
    {
        dispatch(new DynamicTableInsertion($this->id));
    }

    protected function generateShortCode()
    {
        $this->short_code = Str::random(8);
    }
}
