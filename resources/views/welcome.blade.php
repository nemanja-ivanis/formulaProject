<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Formula 1 App</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    </head>
    <body>
    <div class="flex-center position-ref full-height" id="app">
        <div class="container">
            <data-table
                    fetch-url="{{ route('races.table') }}"
                    :columns="['season', 'driver_position', 'driver_name' , 'car_constructor', 'time', 'status', 'driver_number' ,'race_name', 'race_datetime']"
            ></data-table>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
