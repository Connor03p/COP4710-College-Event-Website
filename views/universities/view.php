<?php
    if(!isset($_SESSION['university']))
    {
        header('Location: /universities');
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$data['name']?></title>
        <meta name="description" content="">
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <main>
            <header>
                <h1><?=$data['name']?></h1>
            </header>
            <div class="break-line"></div>

            <div class="center">
                <img src="/api/universities/logo.php?id=<?=$_SESSION['university']['id']?>" alt="University Logo" class="logo">
            </div>

            <div class="center">
                <p><?=$data['description']?></p>
            </div>

            <div class="center">
                <a href="/universities/edit.php?id=<?=$_SESSION['university']['id']?>">Edit</a>
            </div>
        </main>
    </body>
</html>