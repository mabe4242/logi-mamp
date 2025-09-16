<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Enums\RequestStatus;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', RequestStatus::PENDING);
        $attendanceRequests = AttendanceRequest::with('user')
            ->status($status)->latestOrder()->get();

        return view('admin.request_index', compact('attendanceRequests', 'status'));
    }
}
