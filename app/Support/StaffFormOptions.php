<?php

namespace App\Support;

use App\Models\InternalTask;
use App\Models\Project;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;

class StaffFormOptions
{
    /**
     * @return array<string, string>
     */
    public static function employmentTypes(): array
    {
        return [
            'full_time' => __('Full time'),
            'part_time' => __('Part time'),
            'contract' => __('Contract'),
            'intern' => __('Intern'),
            'consultant' => __('Consultant'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function staffStatuses(): array
    {
        return [
            'active' => __('Active'),
            'inactive' => __('Inactive'),
            'suspended' => __('Suspended'),
            'exited' => __('Exited'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function departmentStatuses(): array
    {
        return [
            'active' => __('Active'),
            'inactive' => __('Inactive'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function assignmentStatuses(): array
    {
        return [
            'active' => __('Active'),
            'inactive' => __('Inactive'),
        ];
    }

    /**
     * @return array<class-string, string>
     */
    public static function assignableTypes(): array
    {
        return [
            Project::class => __('Project'),
            Tenant::class => __('Tenant'),
            Server::class => __('Server'),
            SupportTicket::class => __('Support ticket'),
            InternalTask::class => __('Internal task'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function assignableOptions(string $type): array
    {
        return match ($type) {
            Project::class => Project::query()->orderBy('name')->pluck('name', 'id')->all(),
            Tenant::class => Tenant::query()->orderBy('company_name')->pluck('company_name', 'id')->all(),
            Server::class => Server::query()->orderBy('name')->pluck('name', 'id')->all(),
            SupportTicket::class => SupportTicket::query()->orderByDesc('id')->limit(200)->pluck('subject', 'id')->all(),
            InternalTask::class => InternalTask::query()->orderByDesc('id')->pluck('title', 'id')->all(),
            default => [],
        };
    }
}
