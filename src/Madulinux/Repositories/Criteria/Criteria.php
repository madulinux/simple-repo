<?php

namespace Madulinux\Repositories\Criteria;

use Madulinux\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Criteria
 * @package Madulinux\Repositories\Criteria;
 */
abstract class Criteria
{
    /**
     * @param Model $model
     * @param BaseRepository $repository
     * @return mixed
     */
    public abstract function apply(Model $model, BaseRepository $repository);
}