<?php

namespace Abix\SystemLifeCycle\Commands;

use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Illuminate\Console\Command;

class SystemLifeCycleModelCleanUpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-life-cycle:completed-models-clean-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up the logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        SystemLifeCycleModel::where(
            'updated_at',
            '<',
            now()->subDays(config('systemLifeCycle.completed_model_retention_days'))
        )->completed()
            ->delete();

        return 0;
    }
}
