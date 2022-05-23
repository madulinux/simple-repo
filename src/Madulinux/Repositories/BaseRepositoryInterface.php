<?php
namespace Madulinux\Repositories;

use Madulinux\Repositories\Criteria\Criteria;

/**
 * Interface BaseRepositoryInterface
 * @package Madulinux\Repositories
 */
interface BaseRepositoryInterface
{
    /**
     * Retrieve data array for populate field select
     *
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function lists($column, $key = null);

     /**
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null);

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * @param array $columns
     * @return mixed
     */
    public function get($columns = ['*']);

    /**
     * Simple pagination
     * @param int $page
     * @param int $per_page
     * @param array $search_fields
     * @param string $search
     * 
     * @return mixed
     */
    public function pagination(int $page = 1, int $per_page = 0, array $search_fields = [], string $search = "");
    
    /**
     * jquery datatable default request (draw, columns, order, start, length, search)
     * @param array $request
     * @return mixed
     */
    public function datatable(array $request);

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * @param string $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByField(string $field, $value, $columns = ['*']);


    /**
     * @param $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere(array $where, $columns = array('*'));

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = array('*'));

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = array('*'));

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereBetween($field, array $values, $columns = array('*'));

    /**
     * @param array $columns
     * @return mixed
     */
    public function first($columns = ['*']);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function firstOrCreate(array $attributes = []);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function firstOrNew(array $attributes = []);

    /**
     * @param array $data
     * @param $id
     * 
     * @return mixed
     */
    public function update(array $data, $id);


    /**
     * @param string $field
     * @param $value
     * @param array $data
     * 
     * @return mixed
     */
    public function updateBy(string $field, $value, array $data);

    /**
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * @param $id
     * @param bool $force
     * @return mixed
     */
    public function delete($id, $force = false);

    /**
     * @param array $where
     * @param bool $force
     * @param $value
     * @return mixed
     */
    public function deleteWhere(array $where, $force = false);


    /**
     * Check if entity has relation
     *
     * @param string $relation
     *
     * @return $this
     */
    public function has($relation);

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations);

    /**
     * @param  mixed $relations
     * @return $this
     */
    public function withCount($relations);
    

    /**
     * @param string $relation
     * @param closure $closure
     *
     * @return $this
     */
    public function whereHas($relation, $closure);
    
    /**
     * where conditions
     * @param array $conditions
     * @param bool $or
     * 
     * @return $this
     */
    public function whereConditions(array $conditions, bool $or = false);
    
    /**
     * @param array $fields
     *
     * @return $this
     */
    public function hidden(array $fields);

    /**
     * Set visible fields
     *
     * @param array $fields
     * @return $this
     */
    public function visible(array $fields);


    /**
     * @param mixed $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc');
    
    /**
     * @return $this
     */
    public function inRandomOrder();

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function skip($skip);

    /**
     * @param int $take
     * @return $this
     */
    public function take($take);

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit);
    
    /**
     * @param array $where
     * @param string $column
     * @param bool $reset // clear or reset model and scope
     * 
     * @return int
     */
    public function count(array $where = [], $columns = '*', $reset = true);


    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria);


    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $new_criteria);

    /**
     * Query Scope
     *
     * @param \Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope);

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope();

    public function getColumnListing();
}
