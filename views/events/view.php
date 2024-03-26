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
        <title>Event: <?=$data['title']?></title>
        <meta name="description" content="">
        <link rel="stylesheet" href="style.css">
        <style>
            #details-container {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                max-width: 100%;
            }

            #details-container>.detail {
                flex-grow: 1;
                width: unset;
                gap: 0.5rem;
                min-width: 0;
                display: flex;
                align-items: center;
            }

            #details-container>.detail * {
                margin: 0;
            }
        </style>
    </head>

    <body>
        <main>
            <header>
                <h1><?=$data['title']?></h1>
            </header>
            <div class="break-line"></div>

            <div id="details-container">
                <div class="detail">
                    <?php echo file_get_contents($dir['img'] . "calendar-icon.svg"); ?>
                    <p class="center"><?=date_format($data['date_start'], "H:i")?> - <?=$data['date_end']?></p>
                </div>
                <div class="detail">
                    <?php echo file_get_contents($dir['img'] . "location-icon.svg"); ?>
                    <p class="center"><?=$data['location_id']?></p>
                </div>
            </div>

            <section>
                <p class="center"><?=$data['description']?></p>
            </section>
        </main>
    </body>
</html>