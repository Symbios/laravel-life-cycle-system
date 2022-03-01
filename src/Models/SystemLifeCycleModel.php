<?php

namespace Abix\SystemLifeCycle\Models;

use Abix\SystemLifeCycle\Models\SystemLifeCycle;
use Abix\SystemLifeCycle\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class SystemLifeCycleModel extends Model
{
    use UuidTrait;

    /**
     * Sets the table
     *
     * @var string
     */
    protected $table = 'system_life_cycle_models';

    /**
     * Guarded
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Mutates attributes
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'json',
        'model_id' => 'integer',
        'state' => 'integer',
        'stage' => 'integer',
    ];

    public const PENDING_STATE = 1;

    public const PROCESSING_STATE = 2;

    public const COMPLETED_STATE = 3;

    public const FAILED_STATE = 4;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => SystemLifeCycleModel::PENDING_STATE,
        'attempts' => 0,
    ];

    /**
     * Life cycle
     *
     * @return BelongsTo
     */
    public function lifeCycle()
    {
        return $this->belongsTo(SystemLifeCycle::class, 'system_life_cycle_id', 'id');
    }

    /**
     * Current Stage
     *
     * @return BelongsTo
     */
    public function currentStage()
    {
        return $this->belongsTo(SystemLifeCycleStage::class, 'system_life_cycle_stage_id', 'id');
    }

    /**
     * Model
     *
     * @return Model
     */
    public function model()
    {
        Relation::enforceMorphMap(config('systemLifeCycle.relation_mapping'));

        return $this->morphTo('model');
    }

    /**
     * Filters models that can be executed
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeWhereCanBeExecuted(Builder $builder): Builder
    {
        $time = now();

        return $builder->join(
            'system_life_cycles',
            'system_life_cycles.id',
            'system_life_cycle_models.system_life_cycle_id'
        )->where([
            [
                'active', 1,
            ],
            [
                'starts_at', '<', $time,
            ]
        ])->where(function ($query) use ($time) {
            $query->where('system_life_cycles.ends_at', '>', $time)
                ->orWhereNull('system_life_cycles.ends_at');
        })->where(function ($query) {
            $query->whereNull('executes_at')
                ->orWhereBetween('executes_at', [
                    now()->startOfHour()->toDateTimeString(),
                    now()->endOfHour()->toDateTimeString(),
                ]);
        });
    }

    /**
     * Filters by state
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopePending(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleModel::PENDING_STATE);
    }

    /**
     * Filters by state
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeProcessing(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleModel::PROCESSING_STATE);
    }

    /**
     * Filters by state
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeCompleted(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleModel::COMPLETED_STATE);
    }

    /**
     * Filters by state
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeFailed(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleModel::FAILED_STATE);
    }
}
