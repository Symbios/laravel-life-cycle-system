<?php

namespace Abix\SystemLifeCycle\Traits;

use Abix\SystemLifeCycle\Models\SystemLifeCycle;
use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;
use Illuminate\Database\Eloquent\Relations\Relation;

trait EnableSystemLifeCycles
{
    /**
     * Enables system life cycles
     */
    public function lifeCycles()
    {
        if (config('systemLifeCycle.custom_relation_mapping')) {
            Relation::morphMap(config('systemLifeCycle.relation_mapping'));
        }

        return $this->morphMany(SystemLifeCycleModel::class, 'model');
    }

    /**
     * Attaches model to a live cycle
     *
     * @param string $code
     * @return SystemLifeCycleModel|null
     */
    public function addLifeCycleByCode(string $code): ?SystemLifeCycleModel
    {
        $id = SystemLifeCycle::where('code', $code)->value('id');

        if (!$id) {
            return null;
        }

        $stageId = SystemLifeCycleStage::where(
            'system_life_cycle_id',
            $id
        )->orderBy('order', 'ASC')
            ->limit(1)
            ->value('id');

        return $this->lifeCycles()->firstOrCreate([
            'system_life_cycle_id' => $id,
            'system_life_cycle_stage_id' => $stageId,
        ]);
    }

    /**
     * Gets the life cycle class
     *
     * @return object
     */
    public function getLifeCycleStageByCode(string $code): ?object
    {
        $lifeCycle = $this->lifeCycles()
            ->whereLifeCycleCode($code)
            ->first();

        if (!$lifeCycle) {
            return null;
        }

        $class = optional($lifeCycle->currentStage)->class;

        return new $class($lifeCycle->params);
    }

    /**
     * Sets the new stage
     *
     * @param string $code
     * @return void
     */
    public function setNextLifeCycleStage(string $code): void
    {
        $lifeCycleModel = $this->lifeCycles()
            ->whereLifeCycleCode($code)
            ->first();

        if (!$lifeCycleModel) {
            return;
        }

        $newStage = SystemLifeCycleStage::where('order', '>', $lifeCycleModel->currentStage->order)
            ->where('system_life_cycle_id', $lifeCycleModel->system_life_cycle_id)
            ->orderBy('order', 'ASC')
            ->limit(1)
            ->value('id');

        $newData = [
            'system_life_cycle_stage_id' => $newStage,
            'status' => SystemLifeCycleModel::PENDING_STATE,
        ];

        if (!$newStage) {
            $newData['status'] = SystemLifeCycleModel::COMPLETED_STATE;
        }

        $lifeCycleModel->update($newData);
    }

    /**
     * Removes a life cycle
     *
     * @param string $code
     * @return bool
     */
    public function removeLifeCycle(string $code): bool
    {
        return $this->lifeCycles()
            ->whereLifeCycleCode($code)
            ->delete();
    }
}
