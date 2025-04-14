@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<table>
    <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
    </tr>
    @foreach ($attendances as $attendance)
    <tr>
        <td>
            {{ $attendance->date}}
        </td>
        <td>
            {{ $attendance->clock_in}}
        </td>
        <td>
            {{ $attendance->clock_out}}
        </td>
        <td>
            {{ gmdate('H:i:s', $attendance->total_break)  }}
        </td>
        <td>
            {{ gmdate('H:i:s', $attendance->total_clock)  }}
        </td>
        <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
    </tr>
    @endforeach
</table>
@endsection