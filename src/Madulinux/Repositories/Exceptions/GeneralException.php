<?php
namespace Madulinux\Repositories\Exceptions;

use Exception;
use Throwable;

/**
 * Class General Exception.
 * @package Madulinux\Exception
 */
class GeneralException extends Exception
{
    /**
     * @var
     */
    public $message;

    /**
     * @param string $message
     * @param int @code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null )
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return void
     */
    public function report()
    {

    }
}