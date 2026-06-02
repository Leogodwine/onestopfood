@extends('errors.layout')

@section('title', 'Page Not Found')

@section('icon')
    <div class="error-icon error-icon-muted"><i class="bi bi-search"></i></div>
@endsection

@section('heading', 'Page not found')

@section('message')
    The page you requested does not exist or may have been moved. For your security, please return to the home page and navigate from there.
@endsection
