<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_bases', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('grade_number');
            $table->integer('step_number');
            $table->float('salary_amount');
            $table->integer('salary_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('salary_bases');
    }
}
