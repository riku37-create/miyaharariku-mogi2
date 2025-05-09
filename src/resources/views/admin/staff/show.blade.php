@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/show.css') }}">
@endsection

@php
    $prevMonth = \Carbon\Carbon::parse($month)->subMonth()->format('Y-m');
    $nextMonth = \Carbon\Carbon::parse($month)->addMonth()->format('Y-m');
@endphp

@section('content')
<div class="content">
    <h2 class="ttl">{{ $user->name }}さんの勤怠</h2>
    <div class="month">
        <div class="month-prev">
            <a class="month-prev__link" href="{{ route('admin.staff.show', ['id' => $user->id, 'month' => $prevMonth]) }}">先月</a>
        </div>
        <div class="month-center">{{ $month }}</div>
        <div class="month-next">
            <a class="month-next__link" href="{{ route('admin.staff.show', ['id' => $user->id, 'month' => $nextMonth]) }}">翌月</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
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
                    {{ $attendance->date}}
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