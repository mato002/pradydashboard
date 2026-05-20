<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()->orderBy('group')->orderBy('code')->get()->groupBy('group');

        return view('admin.access-control.permissions.index', compact('permissions'));
    }
}
