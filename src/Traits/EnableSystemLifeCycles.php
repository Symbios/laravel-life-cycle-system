<?php

namespace Abix\SystemLifeCycle\Traits;

use Abix\SystemLifeCycle\Models\SystemLifeCycle;
use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;

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
        $id = optional(
            SystemLifeCycle::select('id')->where('code', $code)->first()
        )->id;

        if (!$id) {
            return null;
        }

        return $this->lifeCycles()->create([
            'system_life_cycle_id' => $id,
        ]);
    }
}
