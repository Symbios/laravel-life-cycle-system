<?php

namespace Abix\SystemLifeCycle\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SystemLifeCycleStage extends Model
{
    use HasFactory;

    /**
     * Sets the table
     *
     * @var string
     */
    protected $table = 'system_life_cycle_stages';

    /**
     * Mutates attributes
     *
     * @var array
     */
    protected $casts = [
        'system_life_cycle_id' => 'integer',
        'order' => 'integer',
        'has_internal_stages' => 'boolean',
    ];

    /**
     * Guarded
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Life Cycle
     *
     * @return BelongsTo
     */
    public function lifeCycle()
    {
        return $this->belongsTo(SystemLifeCycle::class, 'system_life_cycle_id', 'id');
    }
}
