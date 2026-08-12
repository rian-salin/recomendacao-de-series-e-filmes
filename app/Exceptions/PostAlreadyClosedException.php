<?php

namespace App\Exceptions;

use Exception;

class PostAlreadyClosedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Publication is already closed.');
    }
}
