<?php
    if (isset($_POST["submit"]))
    {
        if (!isset($_SESSION['user']))
        {
            header('location: http://cop4710/');
        }

        $new_university = array(
            "super_id" => $_POST['super_id'],
            "name" => $_POST['name'],
            "domain" => $_POST['domain'],
            "longitude" => $_POST['lng'],
            "latitude" => $_POST['lat']
        );

        mysqli_begin_transaction($conn);

        try
        {
            $query = "INSERT INTO locations (description, latitude, longitude) VALUES (?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("sdd", $new_university['name'], $new_university['latitude'], $new_university['longitude']);
            $query->execute();

            $location_id = mysqli_insert_id($conn);

            $query = "INSERT INTO universities (name, super_id, location_id, domain) VALUES (?, ?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("siis", $new_university['name'], $new_university['super_id'], $location_id, $new_university['domain']);
            $query->execute();

            $university_id = mysqli_insert_id($conn);

            mysqli_commit($conn);
            header('location: http://cop4710/');
        }
        catch (Exception $e)
        {
            $restoreInput = $_POST;
            mysqli_rollback($conn);
            $errorMessage = $e;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create University Profile</title>
        <meta name="description" content="">
        <link rel="stylesheet" href="<?=$dir['domain']?>/style.css">
        <link rel="stylesheet" href="<?=$dir['domain']?>/libraries/leaflet/leaflet.css" />
        <script src="<?=$dir['domain']?>/libraries/leaflet/leaflet.js"></script>
        <script src="<?=$dir['domain']?>/libraries/leaflet/plugins/geolet.js"></script>
        <style>
            #map {
                height: 20rem;
                width: 100%;
                border-radius: 0.5rem;
                padding: 0.5rem;
                margin: 0.4rem 0;
                box-sizing: border-box;
            }
        </style>
    </head>
    
    <body>
        <main>
            
            <header style="text-align: center;">
                <h1>New University</h1>
            </header>
            <div class="break-line"></div>            

            <form method="POST" enctype="multipart/form-data">
                <?php if (isset($errorMessage)): ?>
                    <section class="error">
                        <p>Error: <?=$errorMessage?></p>
                    </section>
                <?php else: ?>
                    <p class="center">You have not set up your university's profile yet. Please fill out the form below to get started.</p>
                    <br>
                <?php endif; ?>
                
                <fieldset>
                    <legend>University Information</legend>
                    <div>
                        <label for="name">Name:</label>
                        <div>
                            <input type="text" name="name" id="input-name" required
                                <?php if (isset($restoreInput)) echo "value='" . $restoreInput['name'] . "'";?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="domain">Email Domain:</label>
                        <div>
                            <input type="text" name="domain" value="<?php
                                if (!isset($restoreInput['domain']))
                                {
                                    $email = $_SESSION['user']['email']; 
                                    $domain = substr($email, strpos($email, '@') + 1);
                                    echo $domain;
                                }
                                else
                                {
                                    echo $restoreInput['domain'];
                                }
                            ?>">
                            <span></span>
                        </div>
                    </div>
                </fieldset>

                <input type="hidden" name="super_id" value="<?php echo $_SESSION['user']['id']; ?>">

                <?php
                    if (!isset($restoreInput))
                    {
                        $form_has_location = false;
                    }
                    else
                    {
                        $form_has_location = true;
                        $form_location = array(
                            "latitude" => $restoreInput['lat'],
                            "longitude" => $restoreInput['lng']
                        );
                    }
                ?>

                <fieldset>
                    <legend>Location</legend>
                    
                    <div id="map"></div>
                        
                    <div>
                        <label for="lat">Latitude:</label>
                        <div>
                            <input class="hide-arrows" type="text" name="lat" id="input-lat" pattern="^-?\d*\.?\d+$" required
                                <?php if ($form_has_location) echo "value='" . $form_location['latitude'] . "'";?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="lng">Longitude:</label>
                        <div>
                            <input class="hide-arrows" type="text" name="lng" id="input-lng" pattern="^-?\d*\.?\d+$" required
                                <?php if ($form_has_location) echo "value='" . $form_location['longitude'] . "'";?>
                            >
                            <span></span>
                        </div>
                    </div>
                </fieldset>

                <input type="submit" name="submit" value="Create University">
            </form>
        </main>
    </body>

    <script src="<?=$dir['domain']?>/js/map-input.js"></script>
    <script>
            // If either the latitude or longitude is changed, update the map
            latitude.addEventListener('change', updateMap);
            longitude.addEventListener('change', updateMap);

            function updateMap()
            {
                const lat = parseFloat(latitude.value);
                const lng = parseFloat(longitude.value);
                if (lat && lng)
                {
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                }
            }
    </script>

</html>