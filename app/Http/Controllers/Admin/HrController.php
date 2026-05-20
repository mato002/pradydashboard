<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Hr\HrOverview;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HrController extends Controller
{
    public function index(HrOverview $overview): View
    {
        return view('admin.hr.index', [
            'metrics' => $overview->metrics(),
        ]);
    }
}
