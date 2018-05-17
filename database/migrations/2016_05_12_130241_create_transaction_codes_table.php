<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_name');
            $table->integer('is_taxable');
            $table->string('transaction_type');
            $table->integer('is_regular_transaction');
            $table->timestamps();
            $table->string('cutoff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transaction_codes');
    }
}
