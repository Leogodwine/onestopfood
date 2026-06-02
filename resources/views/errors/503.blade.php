@extends('errors.layout')

@section('title', 'Maintenance')

@section('icon')
    <div class="error-icon error-icon-warning"><i class="bi bi-tools"></i></div>
@endsection

@section('heading', 'We will be back soon')

@section('message')
        {{ optional($exception)->getMessage() ?: 'The site is temporarily unavailable while we perform maintenance. Please check back shortly.' }}
@endsection
