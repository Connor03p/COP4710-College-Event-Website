<?php
    if (isset($_POST["submit"]))
    {
        if (!isset($_SESSION['user']))
        {
            header('location: http://cop4710/');
        }

        mysqli_begin_transaction($conn);

        try
        {
            $new_event = array(
                "privacy" =>            isset($_POST['privacy']) ? $_POST['privacy'] : null,
                "organization_id" =>    isset($_POST['organization_id']) ? $_POST['organization_id'] : null,
                "university_id" =>      isset($_POST['university_id']) ? $_POST['university_id'] : null,
                "title" =>              isset($_POST['title']) ? $_POST['title'] : null,
                "category" =>           isset($_POST['category']) ? $_POST['category'] : null,
                "start_time" =>         isset($_POST['start-time']) ? $_POST['start-time'] : null,
                "end_time" =>           isset($_POST['end-time']) ? $_POST['end-time'] : null,
                "location" =>           isset($_POST['location']) ? $_POST['location'] : null,
                "lat" =>                isset($_POST['lat']) ? $_POST['lat'] : null,
                "lng" =>                isset($_POST['lng']) ? $_POST['lng'] : null,
                "contact" =>            isset($_POST['contact']) ? $_POST['contact'] : null,
                "email" =>              isset($_POST['email']) ? $_POST['email'] : null,
                "phone" =>              isset($_POST['phone']) ? $_POST['phone'] : null,
                "summary" =>            isset($_POST['summary']) ? $_POST['summary'] : null,
                "details" =>            isset($_POST['details']) ? $_POST['details'] : null,
                "user_id" =>            $_SESSION['user']['id']
            );

            $query = "CALL insert_event(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @new_event_id)";
            $query = $conn->prepare($query);
            $query->bind_param("sssssssssddiisss", 
                $new_event['title'],
                $new_event['summary'],
                $new_event['privacy'],
                $new_event['contact'],
                $new_event['email'], 
                $new_event['phone'], 
                $new_event['category'],
                $new_event['details'],
                $new_event['location'],
                $new_event['lat'],
                $new_event['lng'], 
                $new_event['university_id'],
                $new_event['organization_id'],
                $new_event['user_id'],
                $new_event['start_time'], 
                $new_event['end_time'],
                );
            $query->execute();

            // Retrieve the new event ID
            $result = $conn->query("SELECT @new_event_id AS new_event_id");
            $row = $result->fetch_assoc();
            $new_event_id = $row["new_event_id"];

            // Check if the new event ID is set
            if ($new_event_id !== null)
                echo "New event ID: $new_event_id";

            mysqli_commit($conn);
            
            if (isset($new_event_id))
                header('location: ' . $dir['domain'] . '/events?id=' . $new_event_id);
            
            return;
        }
        catch (Exception $e)
        {
            $errorMessage = $e->getMessage();
            if ($e->getMessage() == "Event conflicts with another event at the same location")
            {
                // Retrieve conflicting events
                $conflicting_events_result = $conn->query("SELECT * FROM conflicting_events");
                $conflicting_events = [];
                while ($row = $conflicting_events_result->fetch_assoc()) {
                    $conflicting_events[] = $row;
                }
            }

            mysqli_rollback($conn);

            $restoreInput = $_POST;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create New Event</title>
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
                <h1>New Event</h1>
            </header>
            <div class="break-line"></div>

            <form method="POST" enctype="multipart/form-data">

            <?php if (isset($errorMessage)): ?>
                <section class="error">
                    <p><?=$errorMessage?></p>
                        <?php if (isset($conflicting_events)): ?>
                            <?php
                            foreach ($conflicting_events as $row) {
                                echo "<a href='" . $dir['domain'] . "/events?id=" . $row['id'] . "' target='_blank' rel='noreferrer noopener'>";
                                echo "  <section>";
                                echo "      <h3 class='event-title'>" . $row['title'] . "</h3>";
                                echo "      <div class='details-container'>";
            
                                // Display the event date
                                if (isset($row['event_start']) || isset($row['event_end']))
                                {
                                    echo "          <div class='detail'>";
                                    echo file_get_contents($dir['img'] . "calendar-icon.svg");
                                    echo "              <p>";
                                        if(isset($row['event_start']))
                                        {
                                            $startTime = date("F j, g:ia", strtotime($row['event_start']));
                                            echo ($startTime);
                                        }   
                                        if(isset($row['event_end']))
                                        {
                                            // If the end date is more than 1 day after the start date, display the end date
                                            if (date("Y-m-d", strtotime($row['event_start'])) != date("Y-m-d", strtotime($row['event_end'])))
                                            {
                                                $endTime = date("F j, g:ia", strtotime($row['event_end']));
                                            }
                                            else
                                            {
                                                $endTime = date("g:ia", strtotime($row['event_end']));
                                            }
                                            echo " - " . ($endTime);
                                        }
                                    echo "              </p>";
                                    echo "          </div>";
                                }
            
                                // Display the event location
                                if (isset($row['location_name']))
                                {
                                    echo "          <div class='detail'>";
                                    echo file_get_contents($dir['img'] . "location-icon.svg");
                                    echo "              <p class='event-location'>" . $row['location_name'] . "</p>";
                                    echo "          </div>";
                                }
                                
                                echo "      </div>";
                                echo "      <div class='event-summary'>" . $row['summary'] . "</div>";
                                echo "  </section>";
                                echo "</a>";
                            }
                            ?>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php
                    if ($_SESSION['user']['role'] == "Admin")
                    {
                        $query = "SELECT id, name FROM organizations WHERE admin_id = ?";
                        $query = $conn->prepare($query);
                        $query->bind_param("i", $_SESSION['user']['id']);
                    }
                    else if ($_SESSION['user']['role'] == "Super")
                    {
                        $query = "SELECT id, name FROM organizations WHERE university_id = ?";
                        $query = $conn->prepare($query);
                        $query->bind_param("i", $_SESSION['user']['university_id']);
                    }
                    else
                    {
                        // User shouldn't be able to access this page
                        header('location: http://cop4710/');
                    }

                    $query->execute();
                    $form_organizations = $query->get_result();
                    $form_has_organizations = $form_organizations->num_rows > 0;
                    
                ?>

                <fieldset>
                    <legend>Event Information</legend>

                    <div>
                        <label for="title">Title:</label>
                        <div>
                            <input type="text" name="title" required
                                <?php if (isset($restoreInput)) echo "value='" . $restoreInput['title'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="privacy">Privacy:</label>
                        <select name="privacy" id="input-privacy">
                            <?php if ($_SESSION['user']['role'] == "Super"): ?>
                                <option value="public"
                                    <?php if (isset($restoreInput) && $restoreInput['privacy'] == "public") echo "selected" ?>
                                >Public</option>
                            <?php endif; ?>
                            <option value="private"
                                <?php if (isset($restoreInput) && $restoreInput['privacy'] == "private") echo "selected" ?>
                            >Private</option>
                            <?php if ($_SESSION['user']['role'] == "Admin"): ?>
                                <?php if ($form_has_organizations): ?>
                                    <option value="organization"
                                        <?php if (isset($restoreInput) && $restoreInput['privacy'] == "organization") echo "selected" ?>
                                    >RSO Members</option>
                                <?php endif; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div id="input-organization" hidden>
                        <label for="organization_id">RSO:</label>
                        <select name="organization_id">
                            <?php
                                while ($row = $form_organizations->fetch_assoc())
                                {
                                    echo "<option value='" . $row['id'] . "' " . ((isset($restoreInput['organization_id']) && $restoreInput['organization_id'] == $row['id']) ? "selected" : "") . ">" . $row['name'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <input type="hidden" name="university_id" value="<?=$_SESSION['user']['university_id']?>">

                    <?php
                        // Get the possible enum values for the category column of the events table
                        $query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'events' AND COLUMN_NAME = 'category'";
                        $query = $conn->prepare($query);
                        $query->execute();
                        $result = $query->get_result()->fetch_assoc();
                        $form_categories = explode("','", substr($result['COLUMN_TYPE'], 6, -2));
                    ?>

                    <div>
                        <label for="category">Category:</label>
                        <div>
                            <select name="category" required>
                                <?php
                                    foreach ($form_categories as $category)
                                    {
                                        echo "<option value='" . $category . "'>" . $category . "</option>";
                                    }
                                ?>
                            </select>
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="start-time">Start Time:</label>
                        <div>
                            <input type="datetime-local" name="start-time" id="input-start-time" required
                                <?php if (isset($restoreInput)) echo "value='" . $restoreInput['start-time'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label class="optional" for="end-time">End Time:</label>
                        <div>
                            <input type="datetime-local" name="end-time" id="input-end-time"
                                <?php if (isset($restoreInput)) echo "value='" . $restoreInput['end-time'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="summary">Summary:</label>
                        <div>
                            <textarea style="resize: none;" name="summary" rows="2" maxlength="255" required><?php if (isset($restoreInput)) echo $restoreInput['summary']?></textarea>
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="details">Details:</label>
                        <div> 
                            <textarea resizeable name="details" rows="5" required><?php if (isset($restoreInput)) echo $restoreInput['details']?></textarea>
                            <span></span>
                        </div>
                    </div>
                </fieldset>

                <?php
                    if (!isset($restoreInput))
                    {
                        $query = "SELECT L.description, L.latitude, L.longitude FROM locations L, universities U WHERE L.id = U.location_id AND U.id = ?";
                        $query = $conn->prepare($query);
                        $query->bind_param("i", $_SESSION['user']['university_id']);
                        $query->execute();
                        $form_location = $query->get_result();
                        $form_has_location = $form_location->num_rows > 0;
                        if ($form_has_location)
                        {
                            $form_location = $form_location->fetch_assoc();
                        }
                    }
                    else
                    {
                        $form_has_location = true;
                        $form_location = array(
                            "description" => $restoreInput['location'],
                            "latitude" => $restoreInput['lat'],
                            "longitude" => $restoreInput['lng']
                        );
                    }
                ?>

                <fieldset>
                    <legend>Location</legend>

                    <div>
                        <label for="location">Address or Location Name:</label>
                        <div>
                            <input type="text" name="location" required
                                <?php if ($form_has_location) echo "value='" . $form_location['description'] . "'";?>
                            >
                            <span></span>
                        </div>
                    </div>
                    
                    <div id="map"></div>

                    <style>
                        /* Chrome, Safari, Edge, Opera */
                        input.hide-arrows::-webkit-outer-spin-button,
                        input.hide-arrows::-webkit-inner-spin-button {
                            -webkit-appearance: none;
                            margin: 0;
                        }

                        /* Firefox */
                        input.hide-arrows[type=number] {
                            -moz-appearance: textfield;
                        }
                    </style>
                        
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

                <fieldset>
                    <legend>Contact Information</legend>

                    <div>
                        <label for="contact" class="optional">Name:</label>
                        <div>
                            <input type="text" name="contact"
                                <?php if (isset($restoreInput) && isset($restoreInput['contact'])) echo "value='" . $restoreInput['contact'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="optional">Email:</label>
                        <div>
                            <input type="email" name="email"
                                <?php if (isset($restoreInput) && isset($restoreInput['email'])) echo "value='" . $restoreInput['email'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="optional">Phone:</label>
                        <div>
                            <input type="tel" name="phone"
                                <?php if (isset($restoreInput) && isset($restoreInput['phone'])) echo "value='" . $restoreInput['phone'] . "'" ?>
                            >
                            <span></span>
                        </div>
                    </div>
                </fieldset>

                <input type="submit" name="submit" value="Create Event">
            </form>
        </main>
    </body>
    
    <script src="<?=$dir['domain']?>/js/map-input.js"></script>
    <script>
            const privacy = document.getElementById('input-privacy');
            const organization = document.getElementById('input-organization');
            const start_time = document.getElementById('input-start-time');
            const end_time = document.getElementById('input-end-time');

            privacy.addEventListener('change', function() {
                if (privacy.value === 'organization')
                {
                    organization.hidden = false;
                }
                else
                {
                    organization.hidden = true;
                }
            });

            organization.hidden = privacy.value !== 'organization';

            start_time.min = new Date().toISOString().split('T')[0] + 'T00:00';
            end_time.min = new Date().toISOString().split('T')[0] + 'T00:00';

            start_time.addEventListener('change', function() {
                end_time.min = start_time.value;
            });

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