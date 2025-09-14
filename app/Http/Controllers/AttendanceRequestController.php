<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use App\Traits\HandlesTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    use HandlesTransaction;

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

    public function store(Request $request, $attendanceId)
    {
        $attendance = Attendance::with('breaks')->findOrFail($attendanceId);

        $attendanceRequest = $this->handleTransaction(function () use ($request, $attendance) {
            $clockIn = $request->clock_in ? (clone $attendance->date)->setTimeFromTimeString($request->clock_in) : null;
            $clockOut = $request->clock_out ? (clone $attendance->date)->setTimeFromTimeString($request->clock_out) : null;

            $attendanceRequest = AttendanceRequest::create([
                'user_id'       => Auth::id(),
                'attendance_id' => $attendance->id,
                'request_date'  => $attendance->date,
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'status'        => RequestStatus::PENDING,
                'reason'        => $request->reason,
            ]);

            // BreakRequests 保存
            $breaksInput = $request->input('breaks', []);
            foreach ($breaksInput as $index => $breakData) {
                $breakStart = $breakData['break_start'] ?? null;
                $breakEnd   = $breakData['break_end'] ?? null;

                if ($breakStart || $breakEnd) {
                    BreakRequest::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'break_id'              => $attendance->breaks[$index]->id ?? null,
                        'break_start'           => $breakStart ? (clone $attendance->date)->setTimeFromTimeString($breakStart) : null,
                        'break_end'             => $breakEnd ? (clone $attendance->date)->setTimeFromTimeString($breakEnd) : null,
                    ]);
                }
            }

            return $attendanceRequest;
        }, '勤怠修正申請の登録に失敗しました。');

        // もし handleTransaction がエラーを返した場合は RedirectResponse なのでそのまま return
        if ($attendanceRequest instanceof \Illuminate\Http\RedirectResponse) {
            return $attendanceRequest;
        }

        return redirect()->route('attendance_requests.show', $attendanceRequest->id);
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with('breakRequests')->findOrFail($id);

        return view('user.approve', [
            'attendanceRequest' => $attendanceRequest,
            'breaks' => $attendanceRequest->breakRequests,
        ]);
    }

    public function index($status = null)
    {
        if ($status === null) {
            return redirect()->route('attendance_requests.index', ['status' => RequestStatus::PENDING]);
        }

        $query = AttendanceRequest::where('user_id', Auth::id())
            ->where('status', $status);
        $attendanceRequests = $query->latest()->get();

        return view('user.request_index', compact('attendanceRequests', 'status'));
    }
}
