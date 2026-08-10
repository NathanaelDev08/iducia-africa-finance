<?php

namespace App\Modules\Accounting\Services\Exceptions;

use Exception;

class EntryLockedException extends Exception
{
    public function __construct(string $message = 'Cette écriture est verrouillée et ne peut plus être modifiée.')
    {
        parent::__construct($message);
    }
}
