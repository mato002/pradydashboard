<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseCheckLog;
use Illuminate\View\View;

class LicenseCheckLogController extends Controller
{
    public function index(): View
    {
        $logs = LicenseCheckLog::query()
            ->with(['tenant', 'project'])
            ->orderByDesc('checked_at')
            ->paginate(25);

        return view('admin.license-logs.index', compact('logs'));
    }
}
