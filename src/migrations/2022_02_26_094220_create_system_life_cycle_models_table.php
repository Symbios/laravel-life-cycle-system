<?php

use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLifeCycleModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_life_cycle_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('system_life_cycle_id')->index();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->string('state', 20)
                ->default(SystemLifeCycleModel::PENDING_STATE);
            $table->unsignedBigInteger('system_life_cycle_stage_id')->nullable();
            $table->string('batch', 50)->nullable();
            $table->longText('payload')->nullable();
            $table->dateTime('executes_at')->nullable()->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['model_id', 'model_type']);

            $table->index(['state', 'batch']);

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
        Schema::dropIfExists('system_life_cycle_models');
    }
}
