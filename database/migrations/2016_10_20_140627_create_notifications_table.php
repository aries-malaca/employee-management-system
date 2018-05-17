<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->string('full_name');
            $table->string('position');
            $table->string('picture');
            $table->string('contact');
            $table->string('email');
            $table->string('password');
            $table->string('client_user_data');
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
        Schema::drop('client_users');
    }
}
