<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
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

    public function store()
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

    public function checkout()
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

    public function index(Request $request)
    {
        $user = Auth::user();

        // リクエストされた月を取得
        $month = $request->query('month', Carbon::now()->format('Y/m'));
        $startOfMonth = Carbon::createFromFormat('Y/m', $month)->startOfMonth();
        $endOfMonth   = Carbon::createFromFormat('Y/m', $month)->endOfMonth();

        // 勤怠データを取得 (→ここはモデルにスコープで切り出そう！)
        $attendanceRecords = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->date)->toDateString() => $item];
            });

        // 月の日付を1日ずつループして、ログインユーザーの指定された月の勤怠データをまとめる
        $attendances = collect();
        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte($endOfMonth)) {
            $record = $attendanceRecords->get($currentDate->toDateString());
            if ($record) {
                // 出勤・退勤
                $clockIn  = $record->clock_in ? Carbon::parse($record->clock_in) : null;
                $clockOut = $record->clock_out ? Carbon::parse($record->clock_out) : null;

                // 休憩合計時間（分）
                $totalBreakMinutes = $record->breaks->reduce(function ($carry, $break) {
                    if ($break->break_start && $break->break_end) {
                        $carry += Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
                    }
                    return $carry;
                }, 0);

                // 勤務時間（分）
                $workMinutes = 0;
                if ($clockIn && $clockOut) {
                    $workMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
                }

                $attendances->push((object)[
                    'id' => $record->id,
                    'date' => $currentDate->toDateString(),
                    'date_display' => $currentDate->format('m/d'),
                    'weekday' => $currentDate->isoFormat('ddd'),
                    'clock_in' => $clockIn ? $clockIn->format('H:i') : '',
                    'clock_out' => $clockOut ? $clockOut->format('H:i') : '',
                    'break' => $totalBreakMinutes > 0
                        ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
                        : '',
                    'total_work' => $workMinutes > 0
                        ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                        : '',
                ]);
            } else {
                // 出勤記録なし → 空で表示させる。 (→ここって最初の$attendances = collect()でいけないのかな？)
                $attendances->push((object)[
                    'id' => null,
                    'date' => $currentDate->toDateString(),
                    'date_display' => $currentDate->format('m/d'),
                    'weekday' => $currentDate->isoFormat('ddd'),
                    'clock_in' => '',
                    'clock_out' => '',
                    'break' => '',
                    'total_work' => '',
                ]);
            }

            $currentDate->addDay();
        }

        // クエリパラメータから表示月を取得（デフォルトは今月）
        $currentMonth = $request->query('month', now()->format('Y/m'));
        $carbonMonth = Carbon::createFromFormat('Y/m', $currentMonth);

        // 前月・翌月の値
        $prevMonth = $carbonMonth->copy()->subMonth()->format('Y/m');
        $nextMonth = $carbonMonth->copy()->addMonth()->format('Y/m');

        // それぞれのルートURLを生成
        $prevMonthUrl = route('attendance.index', ['month' => $prevMonth]);
        $nextMonthUrl = route('attendance.index', ['month' => $nextMonth]);

        return view('user.attendance_index', compact('attendances', 'currentMonth', 'prevMonthUrl', 'nextMonthUrl'));
    }
}
