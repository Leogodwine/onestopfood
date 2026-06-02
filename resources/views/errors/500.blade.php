@extends('errors.layout')

@section('title', 'Something Went Wrong')

@section('icon')
    <div class="error-icon error-icon-danger"><i class="bi bi-exclamation-triangle"></i></div>
@endsection

@section('heading', 'Something went wrong')

@section('message')
    We could not complete your request. Our team has been notified. For your security, please return to the home page and try again later.
@endsection
