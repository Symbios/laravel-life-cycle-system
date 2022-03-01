<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLifeCycleStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_life_cycle_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('system_life_cycle_id')->nullable();
            $table->unsignedTinyInteger('order')->default(1);
            $table->string('name');
            $table->string('class');
            $table->timestamps();

            $table->foreign('system_life_cycle_id')
                ->references('id')
                ->on('system_life_cycles')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_life_cycle_stages');
    }
}
