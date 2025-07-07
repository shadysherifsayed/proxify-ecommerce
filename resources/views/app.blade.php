<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title> {{ config('app.name') }} </title>
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
</head>

<body>
    <div id="app"></div>
</body>

</html>
