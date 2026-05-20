<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAutomationRule extends Model
{
    protected $fillable = [
        'name',
        'reminder_after_days',
        'penalty_after_days',
        'suspension_after_days',
        'grace_period_days',
        'penalty_percent',
        'vat_percent',
        'recurring_enabled',
        'auto_send_invoices',
        'auto_send_receipts',
        'auto_generate_pdf',
    ];

    protected function casts(): array
    {
        return [
            'penalty_percent' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'recurring_enabled' => 'boolean',
            'auto_send_invoices' => 'boolean',
            'auto_send_receipts' => 'boolean',
            'auto_generate_pdf' => 'boolean',
        ];
    }

    public static function platform(): self
    {
        return static::query()->firstOrCreate(
            ['name' => 'Platform defaults'],
            [
                'reminder_after_days' => 3,
                'penalty_after_days' => 14,
                'suspension_after_days' => 30,
                'grace_period_days' => 7,
                'penalty_percent' => 2.00,
                'recurring_enabled' => true,
                'auto_send_invoices' => false,
                'auto_send_receipts' => true,
                'auto_generate_pdf' => true,
            ],
        );
    }
}
