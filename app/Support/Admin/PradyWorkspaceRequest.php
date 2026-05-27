<?php

namespace App\Support\Admin;

use Illuminate\Http\Request;

class PradyWorkspaceRequest
{
    public static function isPartial(Request $request): bool
    {
        if ($request->ajax()) {
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
}
