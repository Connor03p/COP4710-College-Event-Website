<?php
    if (isset($_POST["submit"]))
    {
        if (!isset($_SESSION['user']))
        {
            header('location: http://cop4710/');
        }

        $img_upload = require $dir['php'] . '\upload.php';

        if (isset($img_upload))
        {
            $new_university = array(
                "super_id" => $_POST['super_id'],
                "name" => $_POST['name'],
                "domain" => $_POST['domain'],
                "description" => $_POST['description']
            );

            $new_file = $img_upload;

            mysqli_begin_transaction($conn);

            try
            {
                $query = "INSERT INTO images (name, path, type, size) VALUES (?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("ssss", $new_file['name'], $new_file['path'], $new_file['type'], $new_file['size']);
                $query->execute();

                $file_id = mysqli_insert_id($conn);

                $query = "INSERT INTO universities (name, super_id, logo_id, domain, description) VALUES (?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("siis", $new_university['name'], $new_university['super_id'], $file_id, $new_university['domain'], $new_university['description']);
                $query->execute();

                $university_id = mysqli_insert_id($conn);

                mysqli_commit($conn);
                header('location: http://cop4710/');
            }
            catch (Exception $e)
            {
                mysqli_rollback($conn);
                echo $e;
            }
        }
        else
        {
            console_log("Image upload failed");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create University Profile</title>
        <meta name="description" content="">
        <link rel="stylesheet" href="http://cop4710/style.css">
    </head>
    
    <body>
        <main>
            
            <header>
                <h1>University Profile</h1>
            </header>
            <div class="break-line"></div>

            <p class="center">You have not set up your university's profile yet. Please fill out the form below to get started.</p>
            <br>

            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Name</label>
                    <input type="text" name="name" id="input-name" placeholder="University Name" required>
                    <span></span>
                </div>

                <div>
                    <label for="fileToUpload">Logo</label>
                    <input type="file" name="fileToUpload" id="fileToUpload">
                    <span></span>
                </div>

                <div>
                    <label for="description">Description</label>
                    <textarea style="resize: vertical;" name="description" rows="5" required></textarea>
                    <span></span>
                </div>
                <input type="hidden" name="super_id" value="<?php echo $_SESSION['user']['id']; ?>">
                <input type="hidden" name="domain" value="<?php 
                    $email = $_SESSION['user']['email']; 
                    $domain = substr($email, strpos($email, '@') + 1);
                    echo $domain;
                ?>">

                <div>
                    <label for="location">Location:</label>
                    <div>
                        <input type="text" name="location" placeholder="Location Description" required>
                        <span></span>
                    </div>
                    <div id="map"></div>
                    <input type="hidden" name="lat" id="input-lat">
                    <input type="hidden" name="lng" id="input-lng">
                </div>
                <input type="submit" name="submit" value="Create University">
            </form>
        </main>
    </body>
    <script src="<?=$dir['domain']?>/js/map-input.js"></script>
</html>