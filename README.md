# Simple Laravel Repository Pattern

laravel simple repository pattern generator.

## Installation

install using composer

```bash
composer require "madulinux/repository"
```


Check if command make:repository already load

```bash
php artisan list
```
if not listed, run autoload

```bash
composer dump-autoload
```


Publish file config file

```bash
php artisan vendor:publish --tag="repositories"
```

## Usage
### Repository

To generate a repository for User model, use the following command
```bash
php artisan make:repository User
```

To generate a repository with a specific model
```bash
php artisan make:repository Foo --model=Bar
```
#### Repository Class
```php
<?php namespace App\Repositories\Eloquent;

use App\Models\User;
use Madulinux\Repositories\Eloquent\BaseRepository as Repository;
use App\Repositories\UserRepositoryInterface;

/**
 * Class UserRepository
 * @package App\Repositories\Eloquent
 */
class UserRepository extends Repository implements UserRepositoryInterface
{

    /**
     * @return string
     */
    public function model()
    {
        return User::class;
    }
}
```
#### Interface Class
```php
<?php namespace App\Repositories;

use Madulinux\Repositories\BaseRepositoryInterface as Repository;

/**
 * Class UserRepositoryInterface
 * @package App\Repositories
 */
interface UserRepositoryInterface extends Repository
{
    //
}
```
### Criteria


To generate a new global criteria, use the following command
```bash
php artisan make:criteria UserAccess
```

To generate a createria for a specific model
```bash
php artisan make:criteria SeventeenYearsOld --model=Profile
```
```php
<?php namespace App\Repositories\Criteria\Profiles;

use Madulinux\Repositories\Criteria\Criteria;
use Madulinux\Repositories\BaseRepositoryInterface as Repository;

/**
 * Class SeventeenYearsOld
 *
 * @package App\Repositories\Criteria\Profiles
 */
class SeventeenYearsOld extends Criteria {

    /**
     * @param            $model
     * @param Repository $repository
     *
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model;
    }
}
```
## License
The contents of this repository is released under the
[MIT licence](https://choosealicense.com/licenses/mit/)