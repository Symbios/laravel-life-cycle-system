<?php

namespace Abix\SystemLifeCycle\Commands;

use Abix\SystemLifeCycle\Models\SystemLifeCycleLog;
use Illuminate\Console\Command;

class SystemLifeCycleLogsCleanUpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-life-cycle:logs-clean-up';

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
        SystemLifeCycleLog::where(
            'created_at',
            '<',
            now()->subDays(config('systemLifeCycle.log_retention_days'))
        )->delete();

        return 0;
    }
}
