<?php

namespace App\Services\PaymentsGateway\Exceptions;

use RuntimeException;

class PaymentsGatewayTenantAlreadyLinkedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('This dashboard tenant is already linked to Payments Gateway.'));
    }
}
