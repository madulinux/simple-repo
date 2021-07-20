<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Interface namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the interface classes.
    |
    */
    'interface_namespace' => 'App\Repositories',

    /*
    |--------------------------------------------------------------------------
    | Interface path
    |--------------------------------------------------------------------------
    |
    | The path to the interface folder.
    |
    */
    'interface_path' => 'app' . DIRECTORY_SEPARATOR . 'Repositories',

    /*
    |--------------------------------------------------------------------------
    | Repository namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the repository classes.
    |
    */
    'repository_namespace' => 'App\Repositories\Eloquent',

    /*
    |--------------------------------------------------------------------------
    | Repository path
    |--------------------------------------------------------------------------
    |
    | The path to the repository folder.
    |
    */
    'repository_path' => 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . 'Eloquent',

    /*
    |--------------------------------------------------------------------------
    | Criteria namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for the criteria classes.
    |
    */
    'criteria_namespace' => 'App\Repositories\Criteria',

    /*
    |--------------------------------------------------------------------------
    | Criteria path
    |--------------------------------------------------------------------------
    |
    | The path to the criteria folder.
    |
    */
    'criteria_path'=> 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . 'Criteria',

    /*
    |--------------------------------------------------------------------------
    | Model namespace
    |--------------------------------------------------------------------------
    |
    | The model namespace.
    |
    */
    'model_namespace' => 'App\Models'
];