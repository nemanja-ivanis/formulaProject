<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('races', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('driver_position');
            $table->integer('driver_number');
            $table->string('driver_name');
            $table->string('car_constructor');
            $table->integer('laps');
            $table->integer('grid');
            $table->string('time');
            $table->string('status');
            $table->integer('points');
            $table->integer('season');
            $table->string('race_name');
            $table->dateTime('race_datetime');
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
        Schema::dropIfExists('races');
    }
}
