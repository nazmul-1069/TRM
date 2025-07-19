<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('training_id');
            $table->unsignedInteger('training_type_id');
            $table->unsignedInteger('training_mode_id');
            $table->unsignedInteger('training_user_id');
            $table->unsignedInteger('user_id');
            $table->integer('no_of_trainees');
            $table->integer('user_duration', 10);
            $table->integer('approved_duration', 10);
            $table->unsignedInteger('status_id');
            $table->datetime('started_at');
            $table->datetime('ended_at');
            $table->string('location', 1000)->nullable();
            $table->number('training_audience_id', 10)->nullable();
            $table->string('description', 3072)->nullable();
            $table->datetime('created_at');
            $table->datetime('updated_at');
            $table->unsignedInteger('created_by')-> nullable()-> default(null);
            $table->unsignedInteger('updated_by')-> nullable()-> default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_histories');
    }
}
