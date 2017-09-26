<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistanceTableOracle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('oracle')->create('webapp_distances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address_from');
            $table->string('address_to');
            $table->string('address_from_code')->nullable();
            $table->string('address_to_code')->nullable();
            $table->integer('duration_value');
            $table->string('duration_text');
            $table->integer('distance_value');
            $table->string('distance_text');
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
        Schema::connection('oracle')->dropIfExists('webapp_distances');
    }
}
