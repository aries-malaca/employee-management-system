<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayslipSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payslip_sets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category_set');
            $table->integer('category_value');
            $table->integer('generated_by_id');
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->integer('payslip_set_id');
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
        Schema::drop('payslip_sets');
    }
}
