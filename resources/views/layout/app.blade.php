<!DOCTYPE html>
<html>
<head>
    @include('layout.header')
</head>
<body class="@yield('body-class')">
   


    @yield('content')

    {{-- 🔥 VERY IMPORTANT --}}
    @include('layout.footer')

</body>
</html>
