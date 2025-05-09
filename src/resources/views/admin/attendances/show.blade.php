@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/show.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">勤怠詳細</h2>
    <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        <table>
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendance->date->format('Y年m月d日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-') }}">
                    ~
                    <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-') }}">
                    @if ($errors->has('clock_in') || $errors->has('clock_out'))
                        <ul class="error-messages">
                        @foreach ($errors->get('clock_in') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @foreach ($errors->get('clock_out') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
            @foreach ($attendance->breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th> {{-- 休憩番号を表示（indexは0から始まるから+1してる） --}}
                <td>
                    <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->break_start ? $break->break_start->format('H:i') : '-') }}">
                    ~
                    <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->break_end ? $break->break_end->format('H:i') : '-') }}">
                    <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                    @if ($errors->has("breaks.$index.start") || $errors->has("breaks.$index.end"))
                        <ul class="error-messages">
                        @foreach ($errors->get("breaks.$index.start") as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @foreach ($errors->get("breaks.$index.end") as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        <div class="btn-wrap">
            <button class="btn-submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection