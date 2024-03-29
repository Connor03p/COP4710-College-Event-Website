<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    $event_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    $event_id = explode("=", $event_id)[1];

    if (isset($_POST['submit'])) {
        $rating = $_POST['star'];
        $comment = $_POST['comment'];
        $user_id = $_SESSION['user']['id'];

        if (isset($rating))
        {
            $query = "REPLACE INTO event_ratings (event_id, user_id, rating) VALUES (?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("iii", $event_id, $user_id, $rating);
            $query->execute();
        }

        if (isset($comment))
        {
            $query = "REPLACE INTO event_comments (event_id, user_id, comment) VALUES (?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("iis", $event_id, $user_id, $comment);
            $query->execute();
        }

        unset($_POST);
        header('Location: ' . $dir['domain'] . '/events?id=' . $event_id);
        return;
    }
    
    // Get event information. If the event has a location id, get the location name and coordinates as well
    $query = "SELECT E.title, E.details, E.date_start, E.date_end, L.description location_name, L.latitude, L.longitude
        FROM events E LEFT JOIN locations L ON E.location_id = L.id WHERE E.id = ?";
    $query = $conn->prepare($query);
    $query->bind_param("i", $event_id);
    $query->execute();
    $data = $query->get_result()->fetch_assoc();
    
    if (!$data)
    {
        require $dir['views'] . '404.php';
        return;
    } 
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Event: <?=$data['title']?></title>
        <meta name="description" content="">
        <link rel="stylesheet" href="<?=$dir['domain']?>/style.css">
        <link rel="stylesheet" href="<?=$dir['domain']?>/libraries/leaflet/leaflet.css" />
        <script src="<?=$dir['domain']?>/libraries/leaflet/leaflet.js"></script>
        <script src="<?=$dir['domain']?>/libraries/leaflet/plugins/geolet.js"></script>
        <style>
            #map {
                height: 20rem;
                width: 100%;
                scroll-margin-top: 10vh;
                border-radius: 0.5rem;
                padding: 0.5rem;
                margin: 0.4rem 0;
                box-sizing: border-box;
            }
        </style>
        <style>
            #details-container {
                display: flex;
                flex-direction: column;
                flex-wrap: wrap;
                gap: 1rem;
                max-width: 100%;
                justify-content: center;
            }

            #details-container svg
            {
                width: 1.5rem;
                height: 1.5rem;
            }

            #details-container>.detail {
                width: unset;
                gap: 0.5rem;
                min-width: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #details-container>.detail * {
                margin: 0;
            }

            .hide {
                display: none;
            }

            .clear {
                float: none;
                clear: both;
            }

            .rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: left;
                height: 3rem;
            }

            .rating > label {
                position: relative;
                font-size: 2.5rem;
                top: -1.2rem;
                color: var(--highEmphasis);
                cursor: pointer;
            }

            .rating > label:hover,
            .rating > label:hover ~ label,
            .rating > input.radio-btn:checked ~ label {
                color: transparent;
            }

            .rating > label:hover:before,
            .rating > label:hover ~ label:before,
            .rating > input.radio-btn:checked ~ label:before,
            .rating > input.radio-btn:checked ~ label:before {
                content: "\2605";
                position: absolute;
                left: 0;
                color: #FFD700;
            }
        </style>
    </head>

    <body>
        <main>
            <header>
                <h1><?=$data['title']?></h1>
            </header>

            <div id="details-container">
                <div class="detail">
                    <?php echo file_get_contents($dir['img'] . "calendar-icon.svg"); ?>
                    <p style="text-wrap: nowrap;" class="center">
                        <?php 
                            if(isset($data['date_start']))
                            {
                                $startTime = date("F j, g:ia", strtotime($data['date_start']));
                                echo ($startTime);
                            }   
                            if(isset($data['date_end']))
                            {
                                // If the end date is more than 1 day after the start date, display the end date
                                if (date("Y-m-d", strtotime($data['date_start'])) != date("Y-m-d", strtotime($data['date_end'])))
                                {
                                    $endTime = date("F j, g:ia", strtotime($data['date_end']));
                                }
                                else
                                {
                                    $endTime = date("g:ia", strtotime($data['date_end']));
                                }
                                echo " - " . ($endTime);
                            }
                        ?>
                    </p>
                </div>
                <?php if (isset($data['location_name'])): ?>
                    <div class="detail">
                        <?php echo file_get_contents($dir['img'] . "location-icon.svg"); ?>
                        <a href="#map" class="center">
                            <p style="text-wrap: nowrap;" class="center"><?=$data['location_name']?></p>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <section>
                <p class="center"><?=$data['details']?></p>
            </section>

            <div>
                <div id="map">
                </div>
                
            </div>
            <br><br>

            <?php
                // Check if the user has already reviewed this event
                $query = "SELECT * FROM event_ratings R, event_comments C WHERE R.event_id = ? AND R.user_id = ? AND C.event_id = ? AND C.user_id = ?";
                $query = $conn->prepare($query);
                $query->bind_param("iiii", $event_id, $_SESSION['user']['id'], $event_id, $_SESSION['user']['id']);
                $query->execute();
                $data_review = $query->get_result()->fetch_assoc();
            ?>

            <form method="POST">
                <div>
                    <label for="rating">Rating:</label>
                    <!-- Rating input: https://jsfiddle.net/shadiabuhilal/0kwnb7ph/3/ -->
                    <div class="rating">
                        <input id="star5" name="star" type="radio" value="5" class="radio-btn hide" 
                            <?php if (isset($data_review['rating']) && $data_review['rating'] == 5) echo "checked"; ?>
                        />
                        <label for="star5" >☆</label>
                        <input id="star4" name="star" type="radio" value="4" class="radio-btn hide" 
                            <?php if (isset($data_review['rating']) && $data_review['rating'] == 4) echo "checked"; ?>
                        />
                        <label for="star4" >☆</label>
                        <input id="star3" name="star" type="radio" value="3" class="radio-btn hide" 
                            <?php if (isset($data_review['rating']) && $data_review['rating'] == 3) echo "checked"; ?>
                        />
                        <label for="star3" >☆</label>
                        <input id="star2" name="star" type="radio" value="2" class="radio-btn hide" 
                            <?php if (isset($data_review['rating']) && $data_review['rating'] == 2) echo "checked"; ?>
                        />
                        <label for="star2" >☆</label>
                        <input id="star1" name="star" type="radio" value="1" class="radio-btn hide" 
                            <?php if (isset($data_review['rating']) && $data_review['rating'] == 1) echo "checked"; ?>
                        />
                        <label for="star1" >☆</label>
                        <div class="clear"></div>
                    </div>
                </div>
                <div>
                    <label for="comment">Comment</label>
                    <div>
                        <textarea style="resize: vertical;" name="comment" rows="4"><?php if (isset($data_review['comment'])) echo $data_review['comment']; ?></textarea>
                        <span></span>
                    </div>
                </div>
                <input type="submit" name="submit" value="<?=(isset($data_review)) ? "Update review" : "Submit review" ?>">
            </form>
            <div class="break-line"></div>
            <?php
                $query = "SELECT U.username, R.rating, C.comment, C.date 
                    FROM event_comments C, event_ratings R, users U 
                    WHERE C.event_id = ? 
                    AND R.event_id = ? 
                    AND U.id = C.user_id 
                    AND U.id = R.user_id 
                    ORDER BY C.date DESC";
                $query = $conn->prepare($query);
                $query->bind_param("ii", $event_id, $event_id);
                $query->execute();
                $data_comments = $query->get_result();

                if ($data_comments->num_rows > 0):
            ?>                
                <h3>Comments</h3>
                <div id="comments-container">
                    <?php while ($comment = $data_comments->fetch_assoc()): ?>
                        <section class="comment">
                            <h4><?=$comment['username']?> - <?=date("F j, g:ia", strtotime($comment['date']));?></h4>
                            <p><?=$comment['comment']?></p>
                        </section>
                    <?php endwhile; ?>
                </div>
            
            <?php else: ?>
                <p class="center">No comments yet</p>
            <?php endif; ?>
        </main>
    </body>
    <script>
        var latitude = <?=$data['latitude']?>;
        var longitude = <?=$data['longitude']?>;
        var mapContainer = document.getElementById('map-container');
        var mapLoaded = false;

        var map = L.map('map').setView([latitude, longitude], 15);
        marker = L.marker([latitude, longitude], { draggable: false }).addTo(map);

        var ourCustomControl = L.Control.extend({
            options: {
                position: 'topright' 
            },

            onAdd: function (map) {
                var container = L.DomUtil.create('a', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.style.backgroundColor = 'white';
                container.style = 
                    "text-decoration: none;" +
                    "color: black;" +
                    "background-color: white;" +
                    "border-radius: 4px;" +
                    "padding: 0.5rem;" +
                    "box-sizing: border-box;" +
                    "cursor: pointer;";
                container.innerText = "Directions";
                container.href = "https://www.google.com/maps/dir/?api=1&destination=" + latitude + "%2C" + longitude;
                return container;
            }
        });

        map.addControl(new ourCustomControl());

        function displayLocation() {
            mapContainer.hidden = !mapContainer.hidden;

            if (!mapLoaded)
            {
                mapLoaded = true;
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);
            }
        }
    </script>
</html>