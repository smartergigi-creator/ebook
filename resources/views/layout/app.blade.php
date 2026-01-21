<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout.header')
</head>
{{-- <body> --}}
    <body class="@yield('body-class')">


    @yield('content')

    @include('layout.footer')


</body>
</html>
