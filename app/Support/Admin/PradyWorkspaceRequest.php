<?php

namespace App\Support\Admin;

use Illuminate\Http\Request;

class PradyWorkspaceRequest
{
    public const FRAME_MAIN = 'prady-workspace';

    public const FRAME_TENANT = 'tenant-workspace';

    public static function isPartial(Request $request): bool
    {
        if ($request->ajax()) {
            return true;
        }

        $turboFrame = $request->header('Turbo-Frame');
        if (in_array($turboFrame, [self::FRAME_MAIN, self::FRAME_TENANT], true)) {
            return true;
        }

        if (in_array($request->header('X-Prady-Workspace'), ['1', 'partial'], true)) {
            return true;
        }

        if ($request->header('X-Tenant-Workspace') === '1') {
            return true;
        }

        return $request->boolean('partial');
    }

    public static function isTenantPanelPartial(Request $request): bool
    {
        if ($request->header('Turbo-Frame') === self::FRAME_TENANT) {
            return true;
        }

        return $request->header('X-Tenant-Workspace') === '1';
    }
}
