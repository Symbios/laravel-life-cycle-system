<?php

namespace Abix\SystemLifeCycle;

use Abix\SystemLifeCycle\Contracts\SystemLifeCycleContract;
use Abix\SystemLifeCycle\Models\SystemLifeCycleLog;
use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class SystemLifeCycleService implements SystemLifeCycleContract
{
    /**
     * Params
     *
     * @var array
     */
    protected $params = [];

    /**
     * Model
     *
     * @var Model
     */
    protected $model = null;

    /**
     * SystemLifeCycleModel
     *
     * @var SystemLifeCycleModel
     */
    protected $systemLifeCycleModel = null;

    /**
     * Sets the properties
     *
     * @param SystemLifeCycleModel $systemLifeCycleModel
     */
    public function __construct(SystemLifeCycleModel $systemLifeCycleModel)
    {
        $this->systemLifeCycleModel = $systemLifeCycleModel;

        $this->params = $systemLifeCycleModel->payload;

        $this->model = $systemLifeCycleModel->model;
    }

    /**
     * Executes the command and checks
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            DB::transaction(function () {
                // Check if model is valid for triggering the handle method
                if (!$this->shouldContinueToNextStage() && !$this->isRetry()) {
                    // Set back to pending state
                    $this->systemLifeCycleModel->update([
                        'status' => SystemLifeCycleModel::PENDING_STATE,
                        'executes_at' => $this->setExecutesAt(),
                    ]);

                    return;
                }

                $this->handle();

                $this->createSuccessLog();

                $this->setNextStage();
            });
        } catch (Exception $e) {
            // Log Error
            $this->createErrorLog($e);
            $this->manageFailedCycle();
        }
    }

    /**
     * Sets the next stage
     *
     * @return void
     */
    public function setNextStage(): void
    {
        $currentStage = $this->systemLifeCycleModel->currentStage;

        $nextStage = SystemLifeCycleStage::where('order', '>', $currentStage->order)
            ->where('system_life_cycle_id', $this->systemLifeCycleModel->system_life_cycle_id)
            ->orderBy('order', 'ASC')
            ->value('id');

        $attributes = [
            'status' => SystemLifeCycleModel::COMPLETED_STATE,
            'payload' => $this->params,
            'executes_at' => null,
        ];

        if ($nextStage) {
            $attributes['status'] = SystemLifeCycleModel::PENDING_STATE;
            $attributes['system_life_cycle_stage_id'] = $nextStage;
            $attributes['attempts'] = 0;
        }

        $this->systemLifeCycleModel->update($attributes);
    }

    /**
     * Failed cycle
     *
     * @return void
     */
    public function manageFailedCycle(): void
    {
        $state = $this->systemLifeCycleModel->attempts >= 3 ?
            SystemLifeCycleModel::FAILED_STATE : SystemLifeCycleModel::PENDING_STATE;

        $this->systemLifeCycleModel->update([
            'status' => $state,
            'attempts' => $this->systemLifeCycleModel->attempts + 1,
            'executes_at' => now()->addHour(),
        ]);
    }

    /**
     * Sets executes at
     *
     * @return Carbon|null
     */
    public function setExecutesAt(): ?Carbon
    {
        return null;
    }

    /**
     * Creates Log
     *
     * @param array $params
     * @return void
     */
    protected function createLog(array $params = [])
    {
        $data = $this->systemLifeCycleModel->only([
            'model_id',
            'model_type',
            'system_life_cycle_stage_id',
            'system_life_cycle_id',
            'payload',
            'attempts',
        ]);

        SystemLifeCycleLog::create(array_merge(
            $data,
            $params,
        ));
    }

    /**
     * Create success log
     *
     * @return void
     */
    protected function createSuccessLog()
    {
        $this->createLog([
            'status' => SystemLifeCycleLog::SUCCESS_STATE,
        ]);
    }

    /**
     * Create error log
     *
     * @param Exception $e
     * @return void
     */
    protected function createErrorLog(Exception $e)
    {
        $this->createLog([
            'error' => $e,
            'status' => SystemLifeCycleLog::FAILED_STATE,
        ]);
    }

    /**
     * Checks if it is a retry
     *
     * @return boolean
     */
    protected function isRetry(): bool
    {
        return $this->systemLifeCycleModel->attempts >= 1;
    }

    /**
     * Sets params
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setParam(string $key, $value): void
    {
        $this->params[$key] = $value;
    }

    /**
     * Gets the value of the param
     *
     * @param string $key
     * @return mixed
     */
    protected function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }
}
