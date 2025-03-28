@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_clock.css') }}">
@endsection

@section('content')
<h1>{{ \Carbon\Carbon::now()->format('H:i:s') }}</h1>
<button>出勤</button>
@endsection