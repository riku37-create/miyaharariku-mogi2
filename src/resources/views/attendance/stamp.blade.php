@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_clock.css') }}">
@endsection

@section('content')
@if (!$attendance)
<div>勤務外</div>
@elseif ($attendance && !$attendance->clock_out)
    @if (!$isOnBreak)
        <div>勤務中</div>
    @endif
    @if ($isOnBreak)
        <div>休憩中</div>
    @endif
@else
<div>退勤済み</div>
@endif
<h1>{{ $today }}</h1>
<h2>{{ $nowTime }}</h2>
@if (!$attendance)
    {{-- まだ出勤してない --}}
    <form action="{{ route('attendance.clockIn') }}" method="POST">
        @csrf
        <button type="submit">出勤</button>
    </form>
@elseif ($attendance && !$attendance->clock_out)
    {{-- 出勤済みだが退勤していない --}}
    @if (!$isOnBreak)
        {{-- 休憩中ではないときだけ退勤ボタンを表示 --}}
        <form action="{{ route('attendance.clockOut') }}" method="POST">
            @csrf
            <button type="submit">退勤</button>
        </form>
    @endif
    @if ($isOnBreak)
        {{-- 休憩中 --}}
        <form action="{{ route('attendance.breakEnd') }}" method="POST">
            @csrf
            <button type="submit">休憩戻り</button>
        </form>
    @else
        {{-- 休憩中ではない --}}
        <form action="{{ route('attendance.breakStart') }}" method="POST">
            @csrf
            <button type="submit">休憩入り</button>
        </form>
    @endif
@else
    {{-- 退勤済み --}}
    <p>お疲れ様でした！</p>
@endif
@endsection