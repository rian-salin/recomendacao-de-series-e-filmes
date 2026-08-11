<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Carbon;

class AccountLockedException extends Exception
{
    public function __construct(public readonly ?Carbon $retryAt = null)
    {
        parent::__construct('Account is locked.');
    }
}
