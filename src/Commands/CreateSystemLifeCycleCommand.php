<?php

namespace Abix\SystemLifeCycle\Commands;

use Abix\SystemLifeCycle\Models\SystemLifeCycle;
use Abix\SystemLifeCycle\Models\SystemLifeCycleStage;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSystemLifeCycleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-life-cycle:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a life cycle';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lifeCycle = [];

        $lifeCycle['name'] = $this->ask('How do you want to name it?');

        $lifeCycle['code'] = $this->checkCode('Do you want to specify the code?');

        $lifeCycle['starts_at'] = $this->checkForDate('When do you want it to start?');

        $lifeCycle['activate_by_cron'] = $this->confirm('Does it activate by the cron job?', true);

        if ($this->confirm('Does it have an end date?', false)) {
            $lifeCycle['ends_at'] = $this->checkForDate('What is the end date');
        }

        $lifeCycle['active'] = 1;

        $lifeCycleId = SystemLifeCycle::create($lifeCycle)->id;

        $stageCount = (int) $this->ask('how many stages do you want?');

        foreach (range(1, $stageCount) as $stageNumber) {
            $stage = [];
            $stage['order'] = $stageNumber;
            $stage['name'] = $this->ask('Stage ' . $stageNumber . ': What is the name for this stage?');
            $stage['system_life_cycle_id'] = $lifeCycleId;
            $stage['class'] = $this->checkClass($stageNumber);

            SystemLifeCycleStage::create($stage);
        }

        return 0;
    }

    /**
     * Checks the class
     *
     * @param int $stageNumber
     * @return void
     */
    public function checkClass(int $stageNumber)
    {
        $class = $this->ask('Stage ' . $stageNumber . ': What is the class for this stage? e.g App\Services\UserJoinLifeCycle');

        if (class_exists($class)) {
            return $class;
        }

        $this->error('We cant find the class');

        return $this->checkClass($stageNumber);
    }

    /**
     * Checks the date
     *
     * @param string $question
     * @return void
     */
    public function checkForDate(string $question)
    {
        $date = $this->ask($question);

        try {
            $date = Carbon::parse($date);

            return $date->toDateTimeString();
        } catch (Exception $e) {
            $this->error('Please Provide a date');
            return $this->checkForDate($question);
        }
    }

    /**
     * Code
     *
     * @param string $question
     * @return string
     */
    public function checkCode(string $question)
    {
        $code = $this->ask($question);

        if (!$code) {
            return Str::uuid()->toString();
        }

        $alreadyInUsed = SystemLifeCycle::where('code', $code)->exists();

        if (Str::contains($code, ' ')) {
            $this->error('Code cant have spaces, please choose different one');
            return $this->checkCode('do you want to specify another one?');
        }

        if (Str::length($code) > 50) {
            $this->error('Code cant be more than 50 characters long, please choose different one');
            return $this->checkCode('do you want to specify another one?');
        }

        if ($alreadyInUsed) {
            $this->error('Code already in use, please choose different one');
            return $this->checkCode('Code was already in use, do you want to specify another one?');
        }

        return $code;
    }
}
