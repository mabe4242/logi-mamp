<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\TableHeaders;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceFormatter;
use App\Services\AttendanceService;
use App\Services\CarbonCalc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date'))->startOfDay() : Carbon::today();
        $attendances = AttendanceService::getDailyAttendances($date);
        $attendances = AttendanceFormatter::formatDay($attendances);

        $prevUrl = route('admin.attendance.index', ['date' => $date->copy()->subDay()->toDateString()]);
        $nextUrl = route('admin.attendance.index', ['date' => $date->copy()->addDay()->toDateString()]);
        $headers = TableHeaders::attendanceDaily();

        return view('admin.attendance_index', compact('date', 'prevUrl', 'nextUrl', 'attendances', 'headers'));
    }

    public function staffAttendances(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', Carbon::now()->format('Y/m'));
        $startOfMonth = Carbon::createFromFormat('Y/m', $month)->startOfMonth();
        $endOfMonth   = Carbon::createFromFormat('Y/m', $month)->endOfMonth();

        $attendances = AttendanceService::getMonthlyAttendances($user->id, $startOfMonth, $endOfMonth);
        $attendances = AttendanceFormatter::formatMonth($attendances);

        $months = CarbonCalc::getMonths($month);
        $prevMonthUrl = route('admin.staff_attendance', ['id' => $user->id, 'month' => $months['prevMonth']]);
        $nextMonthUrl = route('admin.staff_attendance', ['id' => $user->id, 'month' => $months['nextMonth']]);
        $headers = TableHeaders::attendanceMonthly();

        return view('admin.staff_attendances', compact('user', 'attendances', 'month', 'prevMonthUrl', 
            'nextMonthUrl', 'headers'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);
        $breaks = $attendance->breaks;

        return view('admin.show', compact('attendance', 'breaks'));
    }

    public function detailOrCreate(User $user, $date)
    {
        $userId = $user->id;;
        $dateCarbon = Carbon::parse($date);
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $dateCarbon->toDateString()],
            ['status' => AttendanceStatus::OFF]
        );

        return redirect()->route('admin.attendance.show', ['id' => $attendance->id]);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            // 1. 日付を作成
            $dateWithYear = $request->year . $request->month_day; // "2025年9月16日"
            $date = Carbon::createFromFormat('Y年n月j日', $dateWithYear)->toDateString(); // "2025-09-16"

            // 2. 出勤・退勤の日時を作成（date + 時刻）
            $clockIn = $request->clock_in ? Carbon::parse($date . ' ' . $request->clock_in) : null;
            $clockOut = $request->clock_out ? Carbon::parse($date . ' ' . $request->clock_out) : null;

            // 3. 勤怠更新
            $attendance->update([
                'date'      => $date,
                'clock_in'  => $clockIn,
                'clock_out' => $clockOut,
                'reason'    => $request->reason,
            ]);

            // 4. 休憩更新
            foreach ($request->breaks ?? [] as $breakData) {
                // 休憩開始/終了が空ならスキップ
                if (empty($breakData['break_start']) && empty($breakData['break_end'])) {
                    continue;
                }

                // 日付部分を結合
                $breakStart = !empty($breakData['break_start']) ? Carbon::parse($date . ' ' . $breakData['break_start']) : null;
                $breakEnd   = !empty($breakData['break_end']) ? Carbon::parse($date . ' ' . $breakData['break_end']) : null;

                if (!empty($breakData['id'])) {
                    // 既存休憩を更新
                    $break = $attendance->breaks->firstWhere('id', $breakData['id']);
                    if ($break) {
                        $break->update([
                            'break_start' => $breakStart,
                            'break_end'   => $breakEnd,
                        ]);
                    }
                } else {
                    // 新規休憩を作成
                    $attendance->breaks()->create([
                        'break_start' => $breakStart,
                        'break_end'   => $breakEnd,
                    ]);
                }
            }
        });

        return redirect()->route('admin.attendance.index');
    }
}
