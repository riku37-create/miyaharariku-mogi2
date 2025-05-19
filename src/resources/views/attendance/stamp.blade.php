@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/stamp.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="status">
        @if (!$attendance)
            <span class="status-duty">勤務外</span>
        @elseif ($attendance && !$attendance->clock_out)
        @if (!$isOnBreak)
            <span class="status-duty">勤務中</span>
        @endif
        @if ($isOnBreak)
            <span class="status-duty">休憩中</span>
        @endif
        @else
            <span class="status-duty">退勤済み</span>
        @endif
    </div>
    <div class="date-wrap">
        <span class="attendance-date">{{ $today->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}</span>
    </div>
    <div class="time-wrap">
        <span class="attendance-time">{{ $nowTime }}</span>
    </div>
    <div class="attendance-buttons">
        @if (!$attendance) {{-- まだ出勤してない --}}
            <form action="{{ route('attendance.clockIn') }}" method="POST" class="attendance-form">
                @csrf
                <button type="submit" class="btn-clock-in">出勤</button>
            </form>
        @elseif ($attendance && !$attendance->clock_out) {{-- 出勤済みだが退勤していない --}}
            @if (!$isOnBreak) {{-- 休憩中ではないときだけ退勤ボタンを表示 --}}
                <form action="{{ route('attendance.clockOut') }}" method="POST" class="attendance-form">
                    @csrf
                    <button type="submit" class="btn-clock-out">退勤</button>
                </form>
            @endif
            @if ($isOnBreak) {{-- 休憩中 --}}
                <form action="{{ route('attendance.breakEnd') }}" method="POST" class="attendance-form">
                    @csrf
                    <button type="submit" class="btn-break-end">休憩戻り</button>
                </form>
            @else {{-- 休憩中ではない --}}
                <form action="{{ route('attendance.breakStart') }}" method="POST" class="attendance-form">
                    @csrf
                    <button type="submit" class="btn-break-start">休憩入り</button>
                </form>
            @endif
        @else {{-- 退勤済み --}}
            <span class="attendance-message">お疲れ様でした！</span>
        @endif
    </div>
</div>
@endsection