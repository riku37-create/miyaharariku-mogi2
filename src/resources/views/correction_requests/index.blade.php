@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h2>修正申請一覧</h2>

<table border="1">
    <thead>
        <tr>
            <th>ステータス</th>
            <th>ユーザー名</th>
            <th>対象日</th>
            <th>申請理由</th>
            <th>申請日</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($correctionRequests as $request)
        <tr>
            <td>
                @if($request->status === 'pending')
                    承認待ち
                @elseif($request->status === 'approved')
                    承認済み
                @else
                    その他
                @endif
            </td>
            <td>{{ $request->user->name }}</td>
            <td>{{ optional($request->attendance)->date }}</td>
            <td>{{ $request->reason }}</td>
            <td>{{ $request->created_at->format('Y-m-d') }}</td>
            <td><a href="{{ route('attendance.detail', ['id' => $request->attendance->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection