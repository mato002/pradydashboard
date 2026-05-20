<?php

namespace App\Support;

class SupportOpsOptions
{
    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            'bug' => __('Bug'),
            'billing' => __('Billing'),
            'training' => __('Training'),
            'setup' => __('Setup'),
            'feature_request' => __('Feature request'),
            'outage' => __('Outage'),
            'account_access' => __('Account access'),
            'integration' => __('Integration'),
            'other' => __('Other'),
        ];
    }

    /** @return array<string, string> */
    public static function priorities(): array
    {
        return [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            'urgent' => __('Urgent'),
        ];
    }

    /** @return array<string, string> */
    public static function ticketStatuses(): array
    {
        return [
            'open' => __('Open'),
            'in_progress' => __('In progress'),
            'waiting_client' => __('Waiting on client'),
            'resolved' => __('Resolved'),
            'closed' => __('Closed'),
        ];
    }

    /** @return array<string, string> */
    public static function sources(): array
    {
        return [
            'phone' => __('Phone'),
            'email' => __('Email'),
            'whatsapp' => __('WhatsApp'),
            'internal' => __('Internal'),
            'client_portal' => __('Client portal'),
            'system_alert' => __('System alert'),
        ];
    }

    /** @return array<string, string> */
    public static function commentTypes(): array
    {
        return [
            'internal_note' => __('Internal note'),
            'client_update' => __('Client update'),
            'status_change' => __('Status change'),
            'resolution' => __('Resolution'),
            'system' => __('System'),
        ];
    }

    /** @return array<string, string> */
    public static function visibilities(): array
    {
        return [
            'internal' => __('Internal only'),
            'client_visible' => __('Client visible'),
        ];
    }

    /** @return array<string, string> */
    public static function channels(): array
    {
        return [
            'phone' => __('Phone'),
            'email' => __('Email'),
            'whatsapp' => __('WhatsApp'),
            'sms' => __('SMS'),
            'meeting' => __('Meeting'),
            'system_notice' => __('System notice'),
            'other' => __('Other'),
        ];
    }

    /** @return array<string, string> */
    public static function directions(): array
    {
        return [
            'inbound' => __('Inbound'),
            'outbound' => __('Outbound'),
            'internal' => __('Internal'),
        ];
    }

    /** @return array<string, string> */
    public static function communicationStatuses(): array
    {
        return [
            'logged' => __('Logged'),
            'pending_follow_up' => __('Pending follow-up'),
            'completed' => __('Completed'),
            'archived' => __('Archived'),
        ];
    }

    /** @return array<string, string> */
    public static function noticeTypes(): array
    {
        return [
            'billing' => __('Billing'),
            'renewal' => __('Renewal'),
            'maintenance' => __('Maintenance'),
            'outage' => __('Outage'),
            'update' => __('Update'),
            'license' => __('License'),
            'training' => __('Training'),
            'general' => __('General'),
        ];
    }

    /** @return array<string, string> */
    public static function severities(): array
    {
        return [
            'info' => __('Info'),
            'warning' => __('Warning'),
            'critical' => __('Critical'),
        ];
    }

    /** @return array<string, string> */
    public static function noticeStatuses(): array
    {
        return [
            'draft' => __('Draft'),
            'sent' => __('Sent'),
            'acknowledged' => __('Acknowledged'),
            'archived' => __('Archived'),
        ];
    }

    /** @return list<string> */
    public static function openTicketStatuses(): array
    {
        return ['open', 'in_progress', 'waiting_client'];
    }
}
