<?php

namespace App\Services\PaymentsGateway\Exceptions;

use RuntimeException;

class PaymentsGatewayLinkException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
