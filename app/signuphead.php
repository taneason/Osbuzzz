<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osbuzzz</title>
    <link rel="shortcut icon" href="/images/logo.png">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    <header>
        <h1>
            <div class = logo>
                <a href="/"><img src="/images/logo.png"></a>
            </div>
            <div class = title>
                <h1><?= $_title ?? 'Untitled' ?></h1>
        </h1>
    </header>
