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
            

            {{-- ここのspanもstatusによって表示を切り替える --}}

            {{-- <span class="status">勤務外</span>
            <p class="attendance-date">2023年6月1日(木)</p>
            <p class="attendance-time">08:00</p>
            <form action="" method="post">
                @csrf
                <button type="submit" class="attendance_btn">出勤</button>
            </form> --}}


            {{-- <span class="status">出勤中</span>
            <p class="attendance-date">2023年6月1日(木)</p>
            <p class="attendance-time">08:00</p>
            <div class="btn__area">
                <form action="" method="post">
                    @csrf
                    <button type="submit" class="attendance_btn">退勤</button>
                </form>
                <form action="" method="post">
                    @csrf
                    <button type="submit" class="break_btn">休憩入</button>
                </form>
            </div> --}}


            {{-- <span class="status">休憩中</span>
            <p class="attendance-date">2023年6月1日(木)</p>
            <p class="attendance-time">08:00</p>
            <form action="" method="post">
                @csrf
                <button type="submit" class="break_btn">休憩戻</button>
            </form> --}}


            <span class="status">退勤済</span>
            <p class="attendance-date">2023年6月1日(木)</p>
            <p class="attendance-time">08:00</p>
            <p class="message">お疲れ様でした。</p>

        </div>
    </div>
@endsection