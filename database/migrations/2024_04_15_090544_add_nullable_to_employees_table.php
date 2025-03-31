<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('date_of_birth')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('birthday')->nullable()->change();
            $table->string('blood_group')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('country_of_birth')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('date_of_birth')->nullable(false)->change();
            $table->string('gender')->nullable(false)->change();
            $table->string('birthday')->nullable(false)->change();
            $table->string('blood_group')->nullable(false)->change();
            $table->string('nationality')->nullable(false)->change();
            $table->string('country_of_birth')->nullable(false)->change();
            $table->string('marital_status')->nullable(false)->change();
        });
    }
};
