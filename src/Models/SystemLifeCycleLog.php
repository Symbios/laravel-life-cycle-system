<?php

namespace Abix\SystemLifeCycle\Models;

use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;
use Abix\SystemLifeCycle\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class SystemLifeCycleLog extends Model
{
    use HasFactory, UuidTrait;

    /**
     * Sets the table
     *
     * @var string
     */
    protected $table = 'system_life_cycle_logs';

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

    /**
     * Success state
     */
    public const SUCCESS_STATE = 1;

    /**
     * Failed state
     */
    public const FAILED_STATE = 2;

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
     * Stage
     *
     * @return BelongsTo
     */
    public function lifeCycleStage()
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
     * Failed
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeFailed(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleLog::FAILED_STATE);
    }

    /**
     * Success
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeSuccess(Builder $builder): Builder
    {
        return $builder->where('state', SystemLifeCycleLog::SUCCESS_STATE);
    }
}
