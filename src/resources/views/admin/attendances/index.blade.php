@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/index.css') }}">
@endsection

@php
    $prevDate = $date->copy()->subDay()->toDateString();
    $nextDate = $date->copy()->addDay()->toDateString();
@endphp

@section('content')
<div class="content">
    <h2 class="ttl">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠</h2>

    <div class="date">
        <div class="date-prev">
            <a class="date-prev__link" href="{{ route('admin.attendances.index', ['date' => $prevDate]) }}">←前日</a>
        </div>
        <div class="date-center">
            <form id="dateForm" action="{{ route('admin.attendances.index') }}" method="get" >
                <input type="date" name="date" value="{{ $date }}" onchange="document.getElementById('dateForm').submit();">
                {{ $date->format('Y/m/d') }}
            </form>
        </div>
        <div class="date-next">
            <a class="date-next__link" href="{{ route('admin.attendances.index', ['date' => $nextDate]) }}">翌日→</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩時間</th>
                <th>実働時間</th>
                <th>総合時間</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>
                    {{ $attendance->user->name }}
                </td>
                <td>
                    {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                </td>
                <td>
                    {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                </td>
                <td>
                    {{ $attendance->formatted_total_break }}
                </td>
                <td>
                    {{ $attendance->formatted_total_clock }}
                </td>
                <td>
                    {{ $attendance->formatted_total_raw }}
                </td>
                <td><a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection