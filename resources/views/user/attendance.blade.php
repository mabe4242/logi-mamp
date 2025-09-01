@extends('layouts.app')

{{-- @section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance.css') }}">
@endsection --}}

@section('content')
    <x-user_header></x-user_header>
    <div class="content">
        {{-- ここのspanもstatusによって表示を切り替える --}}
        <span class="status">勤務外</span>
        <p>2023年6月1日(木)</p>
        <p>08:00</p>
        {{-- ここの{{ route('user.attendance') }}どう書けばいいんだろう、statusによって変わるんだよな --}}
        <form action="" method="post">
            @csrf
            <button type="submit" class="attendance_btn">出勤</button>
        </form>
    </div>
@endsection