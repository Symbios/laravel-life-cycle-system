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

    public const PENDING_STATE = 'pending';

    public const PROCESSING_STATE = 'processing';

    public const COMPLETED_STATE = 'completed';

    public const FAILED_STATE = 'failed';

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
        if (config('systemLifeCycle.custom_relation_mapping')) {
            Relation::morphMap(config('systemLifeCycle.relation_mapping'));
        }

        return $this->morphTo('model');
    }

    /**
     * Filters models that can be executed
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeWhereCanBeExecuted(
        Builder $builder,
        string $startDate = null,
        string $endDate = null,
        bool $onlyByCron = true

    ): Builder {
        $today = now()->toDateTimeString();

        $params = [
            [
                'active', 1,
            ],
            [
                'starts_at', '<', $today,
            ],

        ];

        if ($onlyByCron) {
            $params[] = [
                'activated_by_cron' => $onlyByCron,
            ];
        }

        return $builder->join(
            'system_life_cycles',
            'system_life_cycles.id',
            'system_life_cycle_models.system_life_cycle_id'
        )->where($params)
            ->where(function ($query) use ($today) {
                $query->where('system_life_cycles.ends_at', '>', $today)
                    ->orWhereNull('system_life_cycles.ends_at');
            })->where(function ($query) use ($startDate, $endDate) {
                $query->whereNull('executes_at')
                    ->orWhereBetween('executes_at', [
                        $startDate ?: now()->startOfMinute()->toDateTimeString(),
                        $endDate ?: now()->addMinutes(10)->toDateTimeString(),
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
