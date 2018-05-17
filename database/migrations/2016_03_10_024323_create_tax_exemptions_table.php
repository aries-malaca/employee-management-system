<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxExemptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_exemptions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('tax_exemption_name');
            $table->string('tax_exemption_shortname');
            $table->integer('tax_exemption_active');
            $table->text('tax_exemption_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tax_exemptions');
    }
}
