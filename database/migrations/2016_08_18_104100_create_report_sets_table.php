<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_sets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category_set');
            $table->integer('category_value');
            $table->integer('generated_by_id');
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->string('report_type');
            $table->string('report_url');
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
        Schema::drop('report_sets');
    }
}
