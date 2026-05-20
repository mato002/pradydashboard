<?php

namespace App\Domain\Tenancy\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

final class IntegrationApiErrorFormatter
{
    public static function format(?\Throwable $exception, ?int $httpStatus = null): string
    {
        if ($exception === null) {
            return $httpStatus
                ? __('HTTP :status', ['status' => $httpStatus])
                : __('Request failed.');
        }

        if ($exception instanceof ConnectionException) {
            return __('Could not connect to the endpoint. Check the URL and network access.');
        }

        $message = $exception->getMessage();

        if (self::isTimeoutMessage($message)) {
            $seconds = (int) config('integrations.api_timeout_seconds', 8);

            return __('Connection timed out after :seconds seconds.', ['seconds' => $seconds]);
        }

        if ($exception instanceof RequestException && $exception->response) {
            return __('HTTP :status', ['status' => $exception->response->status()]);
        }

        return Str::limit(self::sanitizeMessage($message), 240);
    }

    public static function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? $message;

        // Strip absolute paths and stack-trace noise from unexpected errors.
        $message = preg_replace('#([A-Z]:\\\\|/)[^\s]+#i', '[path]', $message) ?? $message;
        $message = preg_replace('/\bin\s+\/[^\s]+\.php:\d+\b/i', '', $message) ?? $message;

        if (str_contains(strtolower($message), 'curl error')) {
            return __('Remote endpoint could not be reached.');
        }

        return $message !== '' ? $message : __('Request failed.');
    }

    private static function isTimeoutMessage(string $message): bool
    {
        $lower = strtolower($message);

        return str_contains($lower, 'timed out')
            || str_contains($lower, 'timeout')
            || str_contains($lower, 'operation timed out');
    }
}
