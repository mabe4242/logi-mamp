@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance_request.css') }}">
@endsection

@section('content')
    <x-admin_header />
    <div class="content">
        <div class="attendance__content">
            <div class="attendance__wrapper">
                <p class="attendance__title">勤怠詳細</p>
                @php use App\Enums\RequestStatus; @endphp
                <form action="{{ route('admin.approve', $attendanceRequest->id) }}" class="attendance_detail" method="POST">
                    @csrf
                    <x-attendance-approve-view :attendanceRequest="$attendanceRequest" :breaks="$breaks" />
                    @if($attendanceRequest->status === RequestStatus::PENDING)
                        <button type="submit" class="approve__button">承認</button>
                    @else
                        <button type="submit" class="disabled__button" disabled>承認済み</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
