@extends('layouts.app')

{{-- No title section — layout will render just the site name --}}
@section('description', setting('site_tagline', 'Вашият надежден партньор.'))

@section('content')
    @include('sections.home.hero')
    @include('sections.home.services-preview')
    @include('sections.home.banner')
    @include('sections.home.about-preview')
    @include('sections.home.blog-preview')
    @include('sections.home.faq')
@endsection
