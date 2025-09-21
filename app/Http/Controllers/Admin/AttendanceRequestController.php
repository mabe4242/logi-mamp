<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Enums\RequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', RequestStatus::PENDING);
        $attendanceRequests = AttendanceRequest::with('user')
            ->status($status)->latestOrder()->get();

        return view('admin.request_index', compact('attendanceRequests', 'status'));
    }

    public function show($id)
    {
        // 申請IDから取得
        $attendanceRequest = AttendanceRequest::with(['user', 'breakRequests'])->findOrFail($id);

        // 元の勤怠データ
        $attendance = $attendanceRequest->attendance()->with('breaks')->first();

        // break は Blade で使う
        $breaks = $attendanceRequest->breakRequests ?? $attendance->breaks;

        return view('admin.approve', compact('attendanceRequest', 'attendance', 'breaks'));
    }

    public function approve(Request $request, AttendanceRequest $attendance_correct_request)
    {
        DB::transaction(function () use ($attendance_correct_request) {
            // 1. attendance_requests のステータスを承認済みに
            $attendance_correct_request->update([
                'status'   => RequestStatus::APPROVED,
                'admin_id' => auth()->id(),
            ]);

            // 2. 対応する勤怠(attendances)を更新
            $attendance = $attendance_correct_request->attendance;

            $attendance->update([
                'clock_in'  => $attendance_correct_request->clock_in,
                'clock_out' => $attendance_correct_request->clock_out,
                'reason'    => $attendance_correct_request->reason,
            ]);

            // 3. break_requests をもとに breaks を更新
            $breakRequests = $attendance_correct_request->breakRequests;

            foreach ($breakRequests as $br) {
                if ($br->break_id) {
                    // 既存 break を更新
                    $break = $attendance->breaks()->find($br->break_id);
                    if ($break) {
                        $break->update([
                            'break_start' => $br->break_start,
                            'break_end'   => $br->break_end,
                        ]);
                    }
                } else {
                    // 新規 break を追加
                    $attendance->breaks()->create([
                        'break_start' => $br->break_start,
                        'break_end'   => $br->break_end,
                    ]);
                }
            }
        });

        return redirect()->route('admin.request', $attendance_correct_request->id);
    }
}
