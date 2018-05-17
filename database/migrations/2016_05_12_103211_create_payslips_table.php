<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('company_id');
            $table->integer('batch_id');
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->integer('generated_by_id');
            $table->text('payslip_data');
            $table->enum('status', array('published', 'draft'));
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
        Schema::drop('payslips');
    }
}
