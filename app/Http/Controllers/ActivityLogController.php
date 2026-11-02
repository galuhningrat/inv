<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Tampilkan 50 log per halaman, urutkan dari yang terbaru
        $logs = ActivityLog::with('user')->latest()->paginate(50);

        return view('admin.activity-logs', compact('logs'));
    }
}
