<?php

namespace App\Modules\Accounting\Services\Exceptions;

use Exception;

class UnbalancedEntryException extends Exception
{
    public function __construct(float $debit, float $credit)
    {
        $diff = abs($debit - $credit);
        parent::__construct("Écriture déséquilibrée : Débit={$debit}, Crédit={$credit}, Écart={$diff}");
    }
}
