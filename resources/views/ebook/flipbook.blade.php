@extends('layout.app')

@section('content')
<body class="ebook-view">

<a href="{{ route('ebooks.index') }}" class="ebook-back-btn">← Back</a>

<div id="ebookLoader">
    <div class="loader-box">
        <div class="spinner"></div>
        <p>Loading ebook…</p>
    </div>
</div>

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

        {{-- COVER --}}
        @if(isset($pages[0]))
            <div class="page cover">
                <img src="{{ $pages[0] }}">
            </div>
        @endif

        {{-- INNER PAGES --}}
        @foreach($pages as $i => $img)
            @if($i !== 0)
                <div class="page">
                    <img src="{{ $img }}">
                </div>
            @endif
        @endforeach

    </div>

    {{-- PREV / NEXT --}}
    {{-- <div class="ebook-side-nav">
        <button id="prevPage" class="side-btn prev">◀</button>
        <button id="nextPage" class="side-btn next">▶</button>
    </div> --}}
     {{-- ⬅️➡️ SIDE NAV --}}
                        <div class="ebook-side-nav">
                            <button class="side-btn prev" id="prevPage">
                                <img src="{{ asset('images/back.png') }}" alt="Previous">
                            </button>

                            <button class="side-btn next" id="nextPage">
                                <img src="{{ asset('images/share.png') }}" alt="Next">
                            </button>
                        </div>
</div>

<audio id="flipSound" src="{{ asset('sound/pageflip.mp3') }}" preload="auto"></audio>

{{-- REQUIRED --}}
{{-- <link rel="stylesheet" href="{{ asset('css/ebook.css') }}">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/turn.min.js') }}"></script>
<script src="{{ asset('js/ebook.js') }}"></script> --}}

@endsection
