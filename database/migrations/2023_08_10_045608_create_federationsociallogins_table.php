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
        Schema::create('federationsociallogins', function (Blueprint $table) {
            $table->id();
            $table->string('types');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('enabled')->nullable();
            $table->string('user_matching_mode')->nullable();
            $table->string('user_path')->nullable();
            $table->string('icon')->nullable();
            $table->string('consumer_key')->nullable();
            $table->string('consumer_secret')->nullable();
            $table->string('scopes')->nullable();
            $table->string('authorizationurl')->nullable();
            $table->string('access_tokenurl')->nullable();
            $table->string('profileurl')->nullable();
            $table->foreignId('authentication_flow')->nullable();
            $table->foreignId('enrollment_flow')->nullable();



            $table->boolean('syncu')->nullable();
            $table->boolean('upw')->nullable();
            $table->boolean('syncg')->nullable();
            $table->boolean('etls')->nullable();
            $table->boolean('usufsv')->nullable();
            $table->string('server_uri')->nullable();
            $table->string('tlsv')->nullable();
            $table->string('tlsca')->nullable();
            $table->string('bind_cn')->nullable();
            $table->string('bind_ps')->nullable();
            $table->string('base_dn')->nullable();
            $table->string('upm')->nullable();
            $table->string('gpm')->nullable();
            $table->string('group')->nullable();
            $table->string('audn')->nullable();
            $table->string('agdn')->nullable();
            $table->string('uf')->nullable();
            $table->string('gf')->nullable();
            $table->string('gmf')->nullable();
            $table->string('ouf')->nullable();




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
        Schema::dropIfExists('federationsociallogins');
    }
};
