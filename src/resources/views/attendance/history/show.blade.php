@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/history/show.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">勤怠詳細</h2>
    <form action="{{ route('attendance.correction.request', $attendance->id) }}" method="POST">
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
                    <input type="text" name="clock_in"
                    value="{{ $hasPendingRequest ?
                    $correctionRequest->requestAttendance->corrected_clock_in->format('H:i') : old('clock_in',$attendance->clock_in ? 
                    $attendance->clock_in->format('H:i') : '-') }}"
                    {{ $hasPendingRequest ? 'readonly' : '' }}>
                    〜
                    <input type="text" name="clock_out"
                    value="{{ $hasPendingRequest ?
                    $correctionRequest->requestAttendance->corrected_clock_out->format('H:i') :old('clock_out', $attendance->clock_out ? 
                    $attendance->clock_out->format('H:i') : '-') }}"
                    {{ $hasPendingRequest ? 'readonly' : '' }}>
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
            @php
                $requestBreak = $hasPendingRequest ?
                $correctionRequest->requestBreakTimes->firstWhere('break_time_id', $break->id): null;
            @endphp
                <tr>
                    <th>休憩{{ $index + 1 }}</th> {{-- 休憩番号を表示（indexは0から始まるから+1してる） --}}
                    <td>
                        <input type="text" name="breaks[{{ $index }}][start]"
                        value="{{ $requestBreak ?
                        $requestBreak->corrected_break_start->format('H:i') : old("breaks.$index.start", $break->break_start ? $break->break_start->format('H:i') : '-') }}"
                        {{ $hasPendingRequest ? 'readonly' : '' }}>
                        〜
                        <input type="text" name="breaks[{{ $index }}][end]"
                        value="{{ $requestBreak ?
                        $requestBreak->corrected_break_end->format('H:i') : old("breaks.$index.end", $break->break_end ? $break->break_end->format('H:i') : '-') }}"
                        {{ $hasPendingRequest ? 'readonly' : '' }}>
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
            @php
                $nextIndex = $attendance->breaks->count();
            @endphp
            <tr>
                <th>休憩{{ $nextIndex + 1 }}</th>
                <td>
                    <input type="text" name="breaks[{{ $nextIndex }}][start]" value="{{ old("breaks.$nextIndex.start") }}">
                    ~
                    <input type="text" name="breaks[{{ $nextIndex }}][end]" value="{{ old("breaks.$nextIndex.end") }}">
                    {{-- 新規追加なので hidden ID は不要 --}}
                    @if ($errors->has("breaks.$nextIndex.start") || $errors->has("breaks.$nextIndex.end"))
                        <ul class="error-messages">
                            @foreach ($errors->get("breaks.$nextIndex.start") as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            @foreach ($errors->get("breaks.$nextIndex.end") as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" cols="30" rows="5" {{ $hasPendingRequest ? 'readonly' : '' }}>{{ $hasPendingRequest ? $correctionRequest->reason : old('reason') }}</textarea>
                    @if ($errors->has('reason'))
                        <ul class="error-messages">
                        @foreach ($errors->get('reason') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
        </table>
        <div class="btn-wrap">
            @if ($hasPendingRequest)
            <span class="wait">※承認待ちのため修正はできません。</span>
            @else
            <button class="btn-submit" type="submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection