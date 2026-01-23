@extends('layout.app')

@section('content')
@section('body-class', 'ebook-view')
<a href="{{ route('ebooks.index') }}" class="ebook-back-btn">← Back</a>

{{-- LOADER --}}
<div id="ebookLoader">
    <div class="loader-box">
        <div class="spinner"></div>
        <p>Loading ebook…</p>
    </div>
</div>

{{-- VIEWER --}}
<div id="viewer-wrapper">

    {{-- TOOLBAR --}}
    <div class="viewer-toolbar">
        <button id="zoomIn">+</button>
        <button id="zoomOut">−</button>
        <button id="zoomReset">⟳</button>
        <button id="fullscreenToggle">⛶</button>
    </div>

    {{-- FLIPBOOK --}}
    <div id="flipbook">

        {{-- COVER PAGE --}}
        @if(isset($pages[0]))
            <div class="page cover">
                <img src="{{ $pages[0] }}" alt="Cover">
            </div>
        @endif

        {{-- INNER PAGES --}}
        @foreach($pages as $i => $img)
            @if($i !== 0)
                <div class="page">
                    <img src="{{ $img }}" alt="Page {{ $i + 1 }}">
                </div>
            @endif
        @endforeach

    </div>

    {{-- SIDE NAV --}}
    <div class="ebook-side-nav">
        <button class="side-btn prev" id="prevPage">
            <img src="{{ asset('images/back.png') }}" alt="Previous">
        </button>

        <button class="side-btn next" id="nextPage">
            <img src="{{ asset('images/share.png') }}" alt="Next">
        </button>
    </div>

</div>

{{-- AUDIO --}}
<audio id="flipSound"
       src="{{ asset('sound/pageflip.mp3') }}"
       preload="auto"></audio>

@endsection
