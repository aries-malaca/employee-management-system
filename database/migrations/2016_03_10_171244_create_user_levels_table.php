<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('level_name');
            $table->string('level_role');
            $table->integer('level_active');
            $table->text('levels_as_employees');
            $table->text('levels_as_supervisor');
            $table->text('levels_to_approve');
            $table->text('levels_to_view');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_levels');
    }
}
