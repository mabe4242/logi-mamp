<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceFormatter;
use App\Services\CarbonCalc;
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

    public function staffAttendances(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // リクエストされた月を取得
        $month = $request->query('month', Carbon::now()->format('Y/m'));
        $startOfMonth = Carbon::createFromFormat('Y/m', $month)->startOfMonth();
        $endOfMonth   = Carbon::createFromFormat('Y/m', $month)->endOfMonth();

        // 勤怠データを取得・フォーマットして整形
        $attendances = Attendance::getMonthlyAttendances($user->id, $startOfMonth, $endOfMonth);
        $attendances = AttendanceFormatter::formatMonth($attendances);

        $months = CarbonCalc::getMonths($month);
        $prevMonthUrl = route('admin.staff_attendance', ['id' => $user->id, 'month' => $months['prevMonth']]);
        $nextMonthUrl = route('admin.staff_attendance', ['id' => $user->id, 'month' => $months['nextMonth']]);

        return view('admin.staff_attendances', [
            'user'          => $user,
            'attendances'   => $attendances,
            'month'         => $month,
            'prevMonthUrl'  => $prevMonthUrl,
            'nextMonthUrl'  => $nextMonthUrl,
        ]);
    }
}
