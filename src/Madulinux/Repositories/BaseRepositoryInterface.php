<?php
namespace Madulinux\Repositories;

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
     * @return bool
     */
    public function save(array $data);

    /**
     * @param $id
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
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @param string $field
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function updateBy(string $field, $value, array $data);

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
}