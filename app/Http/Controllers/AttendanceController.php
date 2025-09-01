<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function create(Request $request)
    {
        $userId = Auth::id();
        $today  = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['status' => AttendanceStatus::OFF]
        )->load('breaks');

        return view('user.attendance', compact('attendance'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $now    = now();

        return DB::transaction(function () use ($userId, $now) {
            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $now->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id'  => $userId,
                    'date'     => $now->toDateString(),
                    'status'   => AttendanceStatus::OFF,
                ]);
            }

            if ($attendance->status !== AttendanceStatus::OFF) {
                return back();
            }

            $attendance->update([
                'clock_in' => $now,
                'status'   => AttendanceStatus::WORKING,
            ]);

            return redirect()->route('attendance.create');
        });
    }

    public function checkout(Request $request)
    {
        $userId = Auth::id();
        $now    = now();

        return DB::transaction(function () use ($userId, $now) {
            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $now->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($attendance->status, [AttendanceStatus::WORKING, AttendanceStatus::BREAK], true)) {
                return back();
            }

            // 休憩中なら自動で休憩を閉じる
            if ($attendance->status === AttendanceStatus::BREAK) {
                $open = $attendance->openBreak();
                if ($open) {
                    $open->update(['break_end' => $now]);
                }
            }

            $attendance->update([
                'clock_out' => $now,
                'status'    => AttendanceStatus::FINISHED,
            ]);

            return redirect()->route('attendance.create');
        });
    }
}
