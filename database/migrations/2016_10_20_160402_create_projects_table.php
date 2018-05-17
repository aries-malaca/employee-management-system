<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('project_deadline');
            $table->datetime('project_date_completed');
            $table->datetime('project_last_activity');
            $table->integer('client_id');
            $table->string('project_title');
            $table->text('project_description');
            $table->string('project_status');
            $table->integer('project_leader_id');
            $table->text('project_members_id');
            $table->text('project_data');
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
        Schema::drop('projects');
    }
}
