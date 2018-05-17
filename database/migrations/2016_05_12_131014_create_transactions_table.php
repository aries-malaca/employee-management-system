<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->float('amount');
            $table->integer('employee_id');
            $table->integer('transaction_code_id');
            $table->string('cutoff');
            $table->string('frequency');
            $table->string('notes');
            $table->integer('added_by_id');
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
        Schema::drop('transactions');
    }
}
