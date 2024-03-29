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
                "privacy" => $_POST['privacy'],
                "organization_id" => $_POST['organization_id'],
                "university_id" => $_POST['university_id'],
                "title" => $_POST['title'],
                "category" => $_POST['category'],
                "start_time" => $_POST['start-time'],
                "end_time" => $_POST['end-time'],
                "location" => $_POST['location'],
                "lat" => $_POST['lat'],
                "lng" => $_POST['lng'],
                "email" => $_POST['email'],
                "phone" => $_POST['phone'],
                "summary" => $_POST['summary'],
                "details" => $_POST['details']
            );

            $query = "INSERT INTO locations (description, latitude, longitude) VALUES (?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("sdd", $new_event['location'], $new_event['lat'], $new_event['lng']);
            $query->execute();

            $location_id = mysqli_insert_id($conn);

            $query = "INSERT INTO events (title, category, date_start, date_end, location_id, email, phone, summary, details) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("ssssissss", $new_event['title'], $new_event['category'], $new_event['start_time'], $new_event['end_time'], $location_id, $new_event['email'], $new_event['phone'], $new_event['summary'], $new_event['details']);
            $query->execute();

            $event_id = mysqli_insert_id($conn);

            if ($new_event['privacy'] === 'organization')
            {
                $event_id = mysqli_insert_id($conn);
                $query = "INSERT INTO rso_events (event_id, rso_id) VALUES (?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("ii", $event_id, $new_event['organization_id']);
                $query->execute();
            }
            else if ($new_event['privacy'] === 'private')
            {
                $event_id = mysqli_insert_id($conn);
                $query = "INSERT INTO private_events (event_id, university_id) VALUES (?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("ii", $event_id, $new_event['university_id']);
                $query->execute();
            }

            mysqli_commit($conn);
            header('location: ' . $dir['domain'] . '/events?id=' . $event_id);
            return;
        }
        catch (Exception $e)
        {
            mysqli_rollback($conn);
            echo $e;
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
            <header>
                <h1>New Event</h1>
            </header>
            <div class="break-line"></div>

            <form method="POST" enctype="multipart/form-data">
                <?php
                    $query = "SELECT id, name FROM organizations WHERE admin_id = ?";
                    $query = $conn->prepare($query);
                    $query->bind_param("i", $_SESSION['user']['id']);
                    $query->execute();
                    $form_organizations = $query->get_result();
                    $form_has_organizations = $form_organizations->num_rows > 0;
                ?>

                <div>
                    <label for="privacy">Privacy:</label>
                    <select name="privacy" id="input-privacy">
                        <?php if ($form_has_organizations): ?>
                            <option value="organization" selected>RSO Members</option>
                        <?php endif; ?>
                        <option value="private">Private</option>
                    </select>
                </div>

                <?php if ($form_has_organizations): ?>
                    <div id="input-organization">
                        <label for="organization_id">RSO:</label>
                        <select name="organization_id">
                            <?php
                                while ($row = $form_organizations->fetch_assoc())
                                {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="university_id" value="<?=$_SESSION['user']['university_id']?>">

                <div>
                    <label for="title">Title:</label>
                    <div>
                        <input type="text" name="title" placeholder="Event Title" required
                            <?php if (isset($restoreInput)) echo "value='" . $restoreInput['title'] . "'" ?>
                        >
                        <span></span>
                    </div>
                </div>

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

                <div>
                    <label for="location">Location:</label>
                    <div>
                        <input type="text" name="location" placeholder="Location Description" required
                            <?php if ($form_has_location) echo "value='" . $form_location['description'] . "'";?>
                        >
                        <span></span>
                    </div>
                    <div id="map"></div>
                    <input type="hidden" name="lat" id="input-lat"
                        <?php if ($form_has_location) echo "value='" . $form_location['latitude'] . "'";?>
                    >
                    <input type="hidden" name="lng" id="input-lng"
                        <?php if ($form_has_location) echo "value='" . $form_location['longitude'] . "'";?>
                    >
                </div>
                

                <div>
                    <label for="email">Email:</label>
                    <div>
                        <input type="email" name="email"
                            <?php if (isset($restoreInput) && isset($restoreInput['email'])) echo "value='" . $restoreInput['email'] . "'" ?>
                        >
                        <span></span>
                    </div>
                </div>

                <div>
                    <label for="phone">Phone:</label>
                    <div>
                        <input type="tel" name="phone"
                            <?php if (isset($restoreInput) && isset($restoreInput['phone'])) echo "value='" . $restoreInput['phone'] . "'" ?>
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
                    <textarea style="resize: vertical" name="details" rows="5" required><?php if (isset($restoreInput)) echo $restoreInput['details']?></textarea>
                    <span></span>
                </div>

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

            start_time.min = new Date().toISOString().split('T')[0] + 'T00:00';
            end_time.min = new Date().toISOString().split('T')[0] + 'T00:00';
            start_time.value = new Date().toISOString().split('T')[0] + 'T00:00';

            start_time.addEventListener('change', function() {
                end_time.min = start_time.value;
            });
    </script>
   
</html>