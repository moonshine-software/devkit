<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url" content="{{ route('moonshine.telegram-login') }}">

    @vite('src/main.ts', 'vendor/moonshine-telegram-miniapp')
</head>
<body></body>
</html>
