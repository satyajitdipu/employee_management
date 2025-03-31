<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('redirect_uri')->nullable();
            $table->string('client_secret')->nullable();
            $table->boolean('personal_access_client')->default(false);
            $table->boolean('password_client')->default(false);
            $table->boolean('revoked')->default(false);
            $table->bigInteger('app_id')->nullable();
            $table->text('scopes')->nullable();
            $table->string('provider')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users'); // Assuming you have a 'users' table
            $table->string('allowed_grant_types')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_clients');
    }
};
