<!DOCTYPE html>
<html lang="en">

<head data-baseurl="{{ URL::to('/') }}">
    <meta charset="UTF-8">

    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Favicon Icon -->
    <link rel='icon' type='image/png' sizes='32x32' href="{{ $siteSetting->favicon ?? '/images/favicon.png' }}">

    <!-- Preload Critical Assets -->
    <link rel="preload" href="{{ without_cache('build/js/login_app.js') }}" as="script">
    <link rel="preload" href="{{ asset('images/hero-bg.webp') }}" as="image" type="image/webp">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ URL::to('/') }}" />

    <!-- Laravel Config -->
    <script>
        window.laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseurl: "{{ url('/') }}",
            asset_url: "{{ str()->of(asset('/'))->trim('/') }}",
            storage_url: "{{ str()->of(Storage::url('/'))->trim('/') }}",
        }
    </script>

    <title>{{ config('app.name') }}</title>

    <!-- Css -->
    <link href="{{ without_cache('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ without_cache('admin/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ without_cache('build/admin/css/style.css') }}" rel="stylesheet">
    <link href="{{ without_cache('admin/css/custom.css') }}" rel="stylesheet">

    <style>
        body, .qcms {
            background-color: #f8fafc;
            background-image: url('{{ asset('images/hero-bg.webp') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <!-- Vue Instance -->
    <div id="app">
        <login token="{{ $token ?? '' }}" email="{{ $email ?? '' }}"
            captcha-sitekey="{{ config('captcha.sitekey') }}" appenv="{{ config('captcha.appenv') }}" />
    </div>

    <!-- Script -->
    <script src="{{ without_cache('build/js/login_app.js') }}" defer></script>
</body>

</html>

