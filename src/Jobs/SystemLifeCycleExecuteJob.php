<?php

namespace Abix\SystemLifeCycle\Jobs;

use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SystemLifeCycleExecuteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * System life cycle model
     *
     * @var SystemLifeCycleModel
     */
    protected $systemLifeCycleModel = null;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SystemLifeCycleModel $systemLifeCycleModel)
    {
        $this->systemLifeCycleModel = $systemLifeCycleModel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stage = $this->systemLifeCycleModel->currentStage;
        $class = $stage->class;

        (new $class($this->systemLifeCycleModel))->execute();
    }
}
