<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\UserBreak;

class BreakController extends Controller
{
    public function start($id)
    {
        $attendance = Attendance::findOrFail($id);

        $break = new UserBreak();
        $break->attendance_id = $attendance->id;
        $break->break_start = now();
        $break->save();

        // 勤務状態を休憩中に変更
        $attendance->status = AttendanceStatus::BREAK;
        $attendance->save();

        return back(); //必ずルート名を指定するように書き直そう。
    }

    public function end($id)
    {
        $attendance = Attendance::findOrFail($id);

        $break = UserBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest('break_start')
            ->firstOrFail();

        $break->break_end = now();
        $break->save();

        // 勤務状態を出勤中に戻す
        $attendance->status = AttendanceStatus::WORKING;
        $attendance->save();

        return back();
    }
}
