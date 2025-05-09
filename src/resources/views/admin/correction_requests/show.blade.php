@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/correction_requests/show.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="ttl">修正申請詳細</h2>
    <table>
        <tr>
            <th>理由</th>
            <td>{{ $request->reason }}</td>
        </tr>
        <tr>
            <th>状態</th>
            @if($request->status === 'pending')
            <td>承認待ち</td>
            @elseif($request->status === 'approved')
            <td>承認済み</td>
            @else
            <td>その他</td>
            @endif
        </tr>
    </table>
    <h4>出退勤</h4>
    <table>
        <tr>
            <th>名前</th>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $request->attendance->date->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>元の出勤・退勤</th>
            <td>
                {{ $request->requestAttendance->original_clock_in->format('H:i') }}
                〜
                {{ $request->requestAttendance->original_clock_out->format('H:i') }}
            </td>
        </tr>
        <tr>
            <th>修正後の出勤・退勤</th>
            <td>
                {{ $request->requestAttendance->corrected_clock_in->format('H:i') }}
                〜
                {{ $request->requestAttendance->corrected_clock_out->format('H:i') }}
            </td>
        </tr>
    </table>
    <h4>休憩</h4>
    <table>
        @foreach ($request->requestBreakTimes as $break)
        <tr>
            <th>元の開始・終了</th>
            <td>
                {{ $break->original_break_start->format('H:i') }}
                〜
                {{ $break->original_break_end->format('H:i') }}
            </td>
        </tr>
        <tr>
            <th>修正開始・修正終了</th>
            <td>
                {{ $break->corrected_break_start->format('H:i') }}
                〜
                {{ $break->corrected_break_end->format('H:i') }}
            </td>
        </tr>
        @endforeach
    </table>
    <div class="btn-wrap">
        <form method="POST" action="{{ route('admin.correction_requests.approve', $request->id) }}">
            @csrf
            @method('PUT')
            <button class="btn-submit" type="submit">承認</button>
        </form>
        <form method="POST" action="{{ route('admin.correction_requests.reject', $request->id) }}">
            @csrf
            @method('PUT')
            <button class="btn-submit" type="submit">却下</button>
        </form>
    </div>
</div>
@endsection