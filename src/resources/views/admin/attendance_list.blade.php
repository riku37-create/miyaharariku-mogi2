@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_clock.css') }}">
@endsection

@section('content')
<h1>{{ \Carbon\Carbon::now()->format('Y-m-d') }}の勤怠</h1>
@endsection