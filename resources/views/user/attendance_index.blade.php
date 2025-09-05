@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance_index.css') }}">
@endsection

@section('content')
    <x-user_header></x-user_header>
    <div class="content">
        <div class="attendance__content">
            <div class="attendance__wrapper">
                <p class="attendance__title">勤怠一覧</p>
                <div class="attendance__month">
                    <a href="{{ $prevMonthUrl }}" class="prev__month">
                        <img src="{{ asset('icons/arrow.png') }}" alt="矢印形の画像" class="arrow__left"> 前月
                    </a>
                    <span class="current__month">
                        <img src="{{ asset('icons/month.png') }}" alt="カレンダーの画像" class="month__icon"> {{ $currentMonth }}
                    </span>
                    <a href="{{ $nextMonthUrl }}" class="next__month">
                        翌月 <img src="{{ asset('icons/arrow.png') }}" alt="矢印形の画像" class="arrow__right">
                    </a>
                </div>
                <table class="table__menu">
                    <thead>
                        <tr>
                            <th class="attendance__column">日付</th>
                            <th class="attendance__column">出勤</th>
                            <th class="attendance__column">退勤</th>
                            <th class="attendance__column">休憩</th>
                            <th class="attendance__column">合計</th>
                            <th class="attendance__column">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td class="attendance__deta">
                                    {{ $attendance->date_display }}<span class="weekday">({{ $attendance->weekday }})</span>
                                </td>
                                <td class="attendance__deta">{{ $attendance->clock_in }}</td>
                                <td class="attendance__deta">{{ $attendance->clock_out }}</td>
                                <td class="attendance__deta">{{ $attendance->break }}</td>
                                <td class="attendance__deta">{{ $attendance->total_work }}</td>
                                <td class="attendance__deta">
                                    {{-- <a class="attendance__detail" href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a> --}}
                                    <a class="attendance__detail" href="">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
