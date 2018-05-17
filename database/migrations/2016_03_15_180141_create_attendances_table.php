<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->string('stamp_type');
            $table->increments('id');
            $table->timestamps();
            $table->datetime('date_credited');
            $table->datetime('attendance_stamp');
            $table->integer('employee_id');
            $table->string('in_out');
            $table->datetime('scheduled_stamp');
            $table->string('via');
            $table->string('more_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attendances');
    }
}
