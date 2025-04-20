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
            <td>
                <input type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-') }}">
                ~
                <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-') }}">
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @foreach ($attendance->breaks as $index => $break)
                <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->break_start ? $break->break_start->format('H:i') : '-') }}">
                ~
                <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->break_end ? $break->break_end->format('H:i') : '-') }}">
                <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                @endforeach
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>
                <textarea name="reason" cols="30" rows="5">{{ old('reason') }}</textarea>
            </td>
        </tr>
    </table>
    @if ($hasPendingRequest)
    <p style="color: red;">承認待ち</p>
    @else
    <button type="submit">修正</button>
    @endif
</form>
@endsection