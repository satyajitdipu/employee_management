<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\EmployeeStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->decimal('head_to_face_ratio', 5, 2)->default(1.5);
            $table->string('gender'); // ['male', 'female', 'other']
            $table->date('date_of_birth');
            $table->string('status')->default(EmployeeStatus::ACTIVE);
            $table->date('birthday');
            $table->string('blood_group'); // ['A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−']
            $table->string('nationality');
            $table->string('country_of_birth');
            $table->string('marital_status'); // ['married', 'unmarried']
            $table->unsignedBigInteger('employee_type_id')->nullable();
            $table->json('field')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->string('full_name')->virtualAs('CONCAT(first_name, \' \', IF(middle_name IS NULL OR middle_name = \'\', \'\', CONCAT(middle_name, \' \') ) , last_name)');
            $table->string('employee_code_with_full_name')->virtualAs('CONCAT(employee_code, \': \', full_name)');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('employee_type_id')->references('id')->on('employee_types')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
