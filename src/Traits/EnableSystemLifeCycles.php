<?php

namespace Abix\SystemLifeCycle\Traits;

use Abix\SystemLifeCycle\Models\SystemLifeCycle;
use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;

trait EnableSystemLifeCycles
{
    /**
     * Enables system life cycles
     */
    public function lifeCycles()
    {
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
     * Sets the new stage
     *
     * @param string $code
     * @return void
     */
    public function setNextLifeCycleStage(string $code): void
    {
        $id = SystemLifeCycle::where('code', $code)->value('id');

        $lifeCycleModel = $this->lifeCycles()->where([
            'system_life_cycle_id' => $id,
        ])->first();

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
            'state' => SystemLifeCycleModel::PENDING_STATE,
        ];

        if (!$newStage) {
            $newData['state'] = SystemLifeCycleModel::COMPLETED_STATE;
        }

        $lifeCycleModel->update($newData);
    }

    /**
     * Removes a life cycle
     *
     * @param string $code
     * @return void
     */
    public function removeLifeCycle(string $code): void
    {
        $id = SystemLifeCycle::where('code', $code)->value('id');

        $this->lifeCycles()->where([
            'system_life_cycle_id' => $id,
        ])->delete();
    }
}
