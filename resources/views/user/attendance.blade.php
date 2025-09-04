@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance.css') }}">
@endsection

@section('content')
    <x-user_header></x-user_header>
    <div class="content__wrapper">
        <div class="content">
            @php
                use App\Enums\AttendanceStatus;
            @endphp
            <x-attendance-clock :status="AttendanceStatus::label($attendance->status)"/>
            @if($attendance->status === AttendanceStatus::OFF)
                <form action="{{ route('attendance.store') }}" method="post">
                    @csrf
                    <button type="submit" class="attendance_btn">出勤</button>
                </form>
            @elseif($attendance->status === AttendanceStatus::WORKING)
                <div class="btn__area">
                    <form action="{{ route('attendance.checkout') }}" method="post">
                        @csrf
                        <button type="submit" class="attendance_btn">退勤</button>
                    </form>
                    <form action="{{ route('break.start', $attendance->id) }}" method="post">
                        @csrf
                        <button type="submit" class="break_btn">休憩入</button>
                    </form>
                </div>
            @elseif($attendance->status === AttendanceStatus::BREAK)
                <form action="{{ route('break.end', $attendance->id) }}" method="post">
                    @csrf
                    <button type="submit" class="break_btn">休憩戻</button>
                </form>
            @elseif($attendance->status === AttendanceStatus::FINISHED)
                <p class="message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection