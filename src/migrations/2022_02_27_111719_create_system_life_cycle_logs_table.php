<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLifeCycleLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_life_cycle_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('system_life_cycle_id')->index();
            $table->unsignedBigInteger('system_life_cycle_stage_id')->index();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->unsignedTinyInteger('state')->default(1)->index();
            $table->longText('payload')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->longText('error')->nullable();
            $table->dateTime('created_at')->index();
            $table->dateTime('updated_at')->index();

            $table->index(['model_id', 'model_type']);

            $table->foreign('system_life_cycle_id')
                ->references('id')
                ->on('system_life_cycles')
                ->cascadeOnDelete();

            $table->foreign('system_life_cycle_stage_id')
                ->references('id')
                ->on('system_life_cycle_stages')
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
        Schema::dropIfExists('system_life_cycle_logs');
    }
}
