<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmploymentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employment_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('employment_status_name');
            $table->integer('employment_status_active');
            $table->string('paid_leave_types');
            $table->string('paid_holidays_types');
			$table->string('cola_frequency');
            $table->string('salary_frequency');
            $table->integer('evaluation_months');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('employment_statuses');
    }
}
