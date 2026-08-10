<?php

namespace App\Modules\Hr\Services\Exceptions;

use Exception;

class DuplicateMatriculeException extends Exception
{
    public function __construct(string $matricule)
    {
        parent::__construct("Le matricule {$matricule} existe déjà pour cette entreprise.");
    }
}
