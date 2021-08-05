<?php

namespace Madulinux\Repositories\Console\Commands\Creators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class RepositoryCreator
 * @package Madulinux\Repositories\Console\Commands\Creator
 */
class RepositoryCreator {

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var
     */
    protected $repository;

    /**
     * @var 
     */
    protected $model;

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param mixed $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed
     */
    public function setModel($model)
    {
        return $this->model = $model;
    }

    /**
     * Create repository
     * 
     * @param $repository
     * @param $model
     * @return int
     */
    public function create($repository, $model)
    {
        $out = new ConsoleOutput();
        try {
            $this->setRepository($repository);
            $this->setModel($model);
            $this->createDirectory();

            $createRepository = $this->createClass('repository');
            $createInterface = $this->createClass('interface');

            if ($createRepository) {
                $out->writeln('Repository Class Created');
            }
            if ($createInterface) {
                $out->writeln('Interface Class created');
            }
            if ($createInterface && $createRepository) {
                return 1;
            }
            return 0;

        } catch (\Throwable $th) {
            $out->writeln('Failed');
            throw $th;
        }
        
    }

    public function createDirectory()
    {
        $directory = $this->getDirectory();

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * @param string $type
     * @return mixed
     */
    protected function getDirectory($type = 'repository')
    {

        return $type == 'repository' ? Config::get('repositories.repository_path') : Config::get('repositories.interface_path');
        
    }

    /**
     * @return mixed|string
     */
    protected function getClassName($type = 'repository')
    {
        $result = $this->getRepository();

        if(!Str::endsWith($result, 'Repository')) {
            $result .= 'Repository';
        }
        if ($type == 'interface') {
            $result .= 'Interface';
        }

        return $result;
    }

    /**
     * @return mixed|string
     */
    protected function getInterfaceName()
    {
        $result = $this->getRepository();
        $result = Str::replaceLast('Repository', 'Interface', $result);

        return $result;
    }

    /**
     * @return string
     */
    protected function getModelName(): string
    {
        $model = $this->getModel();

        if (isset($model) && !empty($model)) {
            $result = $model;
        } else {
            $result = Str::studly(Str::singular($this->stripRepositoryName()));
        }

        return $result;
    }

    /**
     * @return string
     */
    public function stripRepositoryName(): string
    {
        $repository = strtolower($this->getRepository());

        $stripped   = str_replace("repository", "", $repository);

        return ucfirst($stripped);
    }

    /**
     * @return array
     */
    protected function getPopulateData()
    {
        $repository_namespace   = Config::get('repositories.repository_namespace');
        $repository_class       = $this->getClassName();
        $interface_namespace   = Config::get('repositories.interface_namespace');
        $interface_class       = $this->getClassName('interface');
        $model_path             = Config::get('repositories.model_namespace');
        $model_name             = $this->getMOdelName();

        return [
            'repository_namespace'  => $repository_namespace,
            'repository_class'      => $repository_class,
            'interface_namespace'   => $interface_namespace,
            'interface_class'       => $interface_class,
            'model_path'            => $model_path,
            'model_name'            => $model_name
        ];
    }

    /**
     * @param string $type 
     */
    protected function getPath($type = 'repository')
    {
        return $this->getDirectory($type) . DIRECTORY_SEPARATOR . $this->getClassName($type) . '.php';
    }

    /**
     * @param string $stub_name
     * @return string
     */
    protected function getStub($stub_name = 'repository')
    {
        return $this->files->get($this->getStubPath() . "{$stub_name}.stub");
    }

    /**
     * @return string
     */
    protected function getStubPath()
    {
        return dirname(__DIR__, 5) . "/resources/stubs/";
    }

    /**
     * @param string $type
     * @return mixed
     */
    protected function populateStub($type = 'repository')
    {
        $populate_data = $this->getPopulateData();
        $stub = $this->getStub($type);

        foreach ($populate_data as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        return $stub;
    }

    protected function createClass($type = 'repository')
    {
        return $this->files->put($this->getPath($type), $this->populateStub($type));
    }

}