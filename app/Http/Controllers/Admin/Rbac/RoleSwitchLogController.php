<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Http\Controllers\Controller;
use App\Models\RoleSwitchLog;
use Illuminate\View\View;

class RoleSwitchLogController extends Controller
{
    public function index(): View
    {
        $logs = RoleSwitchLog::query()
            ->with('user')
            ->latest('created_at')
            ->paginate(30);

        return view('admin.access-control.switch-logs.index', compact('logs'));
    }
}
