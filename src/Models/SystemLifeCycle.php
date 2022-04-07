<?php

namespace Abix\SystemLifeCycle\Models;

use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLifeCycle extends Model
{
    use HasFactory;

    /**
     * Sets the table
     *
     * @var string
     */
    protected $table = 'system_life_cycles';

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
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
        'activate_by_cron' => 'boolean',
    ];

    /**
     * Life Cycle Stages
     *
     * @return HasMany
     */
    public function stages()
    {
        return $this->hasMany(SystemLifeCycleStage::class);
    }

    /**
     * Activates life cycle
     *
     * @return void
     */
    public function activate()
    {
        $this->update([
            'active' => 1,
        ]);
    }

    /**
     * Deactivates life cycle
     *
     * @return void
     */
    public function deactivate()
    {
        $this->update([
            'active' => 0,
        ]);
    }
}
