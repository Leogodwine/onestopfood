@props(['imgClass' => '', 'showText' => false, 'textClass' => ''])

@php
    $brand = $siteName ?? config('app.name', 'One Stop');
@endphp

@if(file_exists(public_path('images/logo 01.webp')))
    <img src="{{ asset('images/logo 01.webp') }}" alt="{{ $brand }}" class="{{ $imgClass }}">
@elseif(file_exists(public_path('images/logo 02.avif')))
    <img src="{{ asset('images/logo 02.avif') }}" alt="{{ $brand }}" class="{{ $imgClass }}">
@elseif(file_exists(public_path('images/one stop food logo 01.jpeg')))
    <img src="{{ asset('images/one stop food logo 01.jpeg') }}" alt="{{ $brand }}" class="{{ $imgClass }}">
@endif
@if($showText)
    <span class="{{ $textClass }}">{{ $brand }}</span>
@endif
