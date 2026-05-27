<?php

namespace App\Support\Admin;

use Illuminate\Http\Request;

class TenantWorkspaceRequest
{
    public static function isPartial(Request $request): bool
    {
        return PradyWorkspaceRequest::isPartial($request);
    }
}
