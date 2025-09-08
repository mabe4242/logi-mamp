<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\UserBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceRequestController extends Controller
{
    public function create($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $attendance->year = Carbon::parse($attendance->date)->format('Y年');
        $attendance->month_day = Carbon::parse($attendance->date)->format('m月d日');
        $attendance->clock_in_formatted = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null;
        $attendance->clock_out_formatted = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;

        $breaks = $attendance->breaks->map(function ($break) {
            return [
                'break_start' => $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : null,
                'break_end' => $break->break_end ? Carbon::parse($break->break_end)->format('H:i') : null,
            ];
        });

        return view('user.attendance_request', compact('attendance', 'breaks'));
    }
}
