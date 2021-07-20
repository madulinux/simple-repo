<?php

namespace Madulinux\Repositories\Console\Commands;

use Illuminate\Console\Command;
use Madulinux\Repositories\Console\Commands\Creators\CriteriaCreator;

/**
 * Class MakeCriteria
 * @package Madulinux\Repositories\Console\Commands\
 */
class MakeCriteriaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:criteria {criteria} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new criteria class';

    /**
     * @var CriteriaCreator
     */
    protected $creator;

    /**
     * @var
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CriteriaCreator $creator)
    {
        parent::__construct();

        $this->creator = $creator;

        $this->composer = app()['composer'];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $criteria = $this->argument('criteria');

        $model      = $this->option('model');

        $this->writeCriteria($criteria, $model);
        
        $this->composer->dumpAutoloads();
    }

    protected function writeCriteria($criteria, $model)
    {
        
        $this->info('Criteria Name : ' . $criteria . ' Model : ' . $model);
        try {
            $make = $this->creator->create($criteria, $model);
            if ($make)
            {
                $this->info('new Criteria created.');
            } else {
                $this->error('Failed!');
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
