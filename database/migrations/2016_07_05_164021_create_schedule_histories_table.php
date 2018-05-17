<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('branch_id');
            $table->text('schedule_data');
            $table->datetime('schedule_start');
            $table->datetime('schedule_end');
            $table->string('schedule_type');
            $table->integer('is_flexi_time');
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
        Schema::drop('schedule_histories');
    }
}
