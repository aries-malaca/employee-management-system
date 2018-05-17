<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('employee_id');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->integer('updated_by_employee_id');
            $table->float('salary_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('salary_histories');
    }
}
