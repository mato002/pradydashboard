<?php

// bootstrap/app.php — merge into ->withMiddleware(function (Middleware $middleware): void { ... })

use App\Http\Middleware\CheckPradyLicense;
use App\Http\Middleware\RequirePradyModule;

$middleware->alias([
    'prady.license' => CheckPradyLicense::class,
    'prady.module' => RequirePradyModule::class,
]);

// Enforce license on authenticated web traffic. Adjust exclusions for your routes.
$middleware->appendToGroup('web', [
    CheckPradyLicense::class,
]);

// Recommended exclusions (Laravel 11) — add before appendToGroup or use middleware priority:
// $middleware->validateCsrfTokens(except: ['webhooks/payments-gateway/*']);
//
// To skip license on specific routes, define a route group without CheckPradyLicense
// or add early-return paths inside CheckPradyLicense for:
//   - /up, /health
//   - /api/system/info
//   - /webhooks/payments-gateway/*
