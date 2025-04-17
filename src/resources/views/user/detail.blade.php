@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_clock.css') }}">
@endsection

@section('content')
<h1>勤怠詳細</h1>
<form action="{{ route('attendance.correction.request', $attendance->id) }}" method="POST">
    @csrf
    <table>
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendance->date }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td><input type="text" name="clock_in" placeholder="{{ $attendance->clock_in }}">~<input type="text" name="clock_out" placeholder="{{ $attendance->clock_out }}"></td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @foreach ($attendance->breaks as $index => $break)
                <input type="text" name="breaks[{{ $index }}][start]" placeholder="{{ $break->break_start }}">~
                <input type="text" name="breaks[{{ $index }}][end]" placeholder="{{ $break->break_end }}">
                <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                @endforeach
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td><textarea name="reason" id="" cols="30" rows="10"></textarea></td>
        </tr>
    </table>
    @if ($hasPendingRequest)
    <p style="color: red;">承認待ち</p>
    @else
    <button type="submit">修正</button>
    @endif
</form>
@endsection