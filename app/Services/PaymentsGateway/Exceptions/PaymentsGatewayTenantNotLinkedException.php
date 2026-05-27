<?php

namespace App\Services\PaymentsGateway\Exceptions;

use RuntimeException;

class PaymentsGatewayTenantNotLinkedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('This dashboard tenant is not linked to Payments Gateway.'));
    }
}
