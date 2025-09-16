<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date'))->startOfDay() : Carbon::today();
        $attendances = Attendance::getDailyAttendances($date);
        $attendances = AttendanceFormatter::formatDay($attendances);

        $prevUrl = route('admin.attendance.index', ['date' => $date->copy()->subDay()->toDateString()]);
        $nextUrl = route('admin.attendance.index', ['date' => $date->copy()->addDay()->toDateString()]);

        return view('admin.attendance_index', compact('date', 'prevUrl', 'nextUrl', 'attendances'));
    }
}
