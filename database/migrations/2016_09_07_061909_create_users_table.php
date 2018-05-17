<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('address');
            $table->string('mobile');
            $table->string('picture');
            $table->integer('level');
            $table->string('employee_no');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name');
            $table->text('birth_date');
            $table->text('hired_date');
            $table->string('skills');
            $table->string('about');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->integer('zip_code');
            $table->string('gender');
            $table->string('civil_status');
            $table->string('telephone');
            $table->integer('bank_code');
            $table->string('bank_number');
            $table->integer('tax_exemption_id');
            $table->string('sss_no');
            $table->string('tin_no');
            $table->string('philhealth_no');
            $table->string('pagibig_no');
            $table->string('hmo_no');
            $table->integer('employee_status');
            $table->integer('active_status');
            $table->integer('position_id');
            $table->integer('department_id');
            $table->integer('company_id');
            $table->integer('batch_id');
            $table->integer('biometric_no');
            $table->string('local_number');
            $table->datetime('next_evaluation');
            $table->integer('allow_access');
            $table->integer('allow_suspension');
            $table->integer('allow_overtime');
            $table->string('birth_place');
            $table->datetime('last_activity');
            $table->float('cola_rate');
            $table->integer('allow_leave');
            $table->integer('allow_adjustment');
            $table->integer('allow_offset');
            $table->integer('allow_travel');
            $table->text('contributions');
            $table->integer('receive_notification');
            $table->string('contact_person');
            $table->string('contact_info');
            $table->string('contact_relationship');
            $table->string('last_location');
            $table->text('email_client_data');
            $table->string('last_ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
