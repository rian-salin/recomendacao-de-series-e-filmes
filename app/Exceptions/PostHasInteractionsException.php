<?php

namespace App\Exceptions;

use Exception;

class PostHasInteractionsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Publication already has interactions from other users.');
    }
}
