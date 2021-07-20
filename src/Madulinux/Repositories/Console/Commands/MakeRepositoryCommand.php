<?php

namespace Madulinux\Repositories\Console\Commands;

use Madulinux\Repositories\Console\Commands\Creators\RepositoryCreator;
use Illuminate\Console\Command;

/**
 * Class MakeRepository
 * @package Madulinux\Repositories\Console\Commands
 */
class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {repository} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * @var RepositoryCreator
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
    public function __construct(RepositoryCreator $creator)
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
        $repository = $this->argument('repository');

        $model      = $this->option('model');

        $this->writeRepository($repository, $model);
        
        $this->composer->dumpAutoloads();
    }

    protected function writeRepository($repository, $model)
    {
        
        $this->info('Repository ' . $repository);
        $this->info('Model ' . $model);
        try {
            $make = $this->creator->create($repository, $model);
            if ($make)
            {
                $this->info('new Repository created.');
            } else {
                $this->warn('make repository Failed');
                $this->error('Failed!');
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
