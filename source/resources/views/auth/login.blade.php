<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        @vite(['resources/js/auth.js'])

        <script lang='text/javascript'>
            window.localStorage.setItem('domains', '{!! base64_encode(json_encode($domains)) !!}');
        </script>
    </head>
    <body>
        @vue
    </body>
</html>
