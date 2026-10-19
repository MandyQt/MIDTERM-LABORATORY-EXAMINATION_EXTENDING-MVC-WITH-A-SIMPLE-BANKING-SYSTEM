<?php

namespace App\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    public function __construct(string $message = 'Insufficient funds: this withdrawal would drop the balance below the minimum balance.')
    {
        parent::__construct($message);
    }
}
