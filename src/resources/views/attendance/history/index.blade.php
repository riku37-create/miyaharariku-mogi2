@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
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
            <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection