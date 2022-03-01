<?php

namespace Abix\SystemLifeCycle\Contracts;

use Abix\SystemLifeCycle\Models\SystemLifeCycleModel;

interface SystemLifeCycleContract
{
    /**
     * Sets the current life cycle model
     *
     * @param SystemLifeCycleModel $systemLifeCycleModel
     */
    public function __construct(SystemLifeCycleModel $systemLifeCycleModel);

    /**
     * Executes
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Runs the code to handle the life cycle stage
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Checks if the model is valid for running the handle method
     *
     * @return boolean
     */
    public function shouldContinueToNextStage(): bool;

    /**
     * Sets the next stage
     *
     * @return void
     */
    public function setNextStage(): void;
}
