<?php
    if (isset($_POST["submit"]))
    {
        if (!isset($_SESSION['user']))
        {
            header('location: ' . $dir['domain']);
        }

        if (isset($_FILES["fileToUpload"]))
        {
            $img_upload = require $dir['php'] . '\upload.php';

            if (!isset($img_upload))
            {
                echo "Image upload failed";
                return;
            }

            $new_file = $img_upload;
        }
        else
        {
            $new_file = null;
            $file_id = null;
        }
        
        $new_organization = array(
            "name" => $_POST['name'],
            "university_id" => $_SESSION['user']['university_id'],
            "summary" => $_POST['summary'],
            "description" => $_POST['description']
        );

        mysqli_begin_transaction($conn);

        try
        {
            if (isset($new_file))
            {
                $query = "INSERT INTO images (name, path, type, size) VALUES (?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("ssss", $new_file['name'], $new_file['path'], $new_file['type'], $new_file['size']);
                $query->execute();

                $file_id = mysqli_insert_id($conn);
            }

            $query = "INSERT INTO organizations (name, image_id, university_id, summary, description) VALUES (?, ?, ?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("siiss", $new_organization['name'], $file_id, $new_organization['university_id'], $new_organization['summary'], $new_organization['description']);
            $query->execute();

            $organization_id = mysqli_insert_id($conn);

            $query = "INSERT INTO rso_members (rso_id, user_id) VALUES (?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("ii", $organization_id, $_SESSION['user']['id']);
            $query->execute();

            mysqli_commit($conn);
            header('location: ' . $dir['domain']);
        }
        catch (Exception $e)
        {
            mysqli_rollback($conn);
            $errorMessage = $e;
            $restoreInput = $_POST;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create New RSO</title>
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
                <h1>New RSO</h1>
            </header>
            <div class="break-line"></div>

            <form method="POST" enctype="multipart/form-data">

                <?php if (isset($errorMessage)): ?>
                    <section class="error">
                        <p>Error: <?=$errorMessage?></p>
                    </section>
                <?php endif; ?>

                <div>
                    <label for="name">Name:</label>
                    <div>
                        <input type="text" name="name" id="input-name" required
                            <?php if (isset($restoreInput['name'])): ?>
                                value="<?=$restoreInput['name']?>"
                            <?php endif; ?>
                        >
                        <span></span>
                    </div>
                </div>

                <div>
                    <label class="optional" for="fileToUpload">Image:</label>
                    <div>
                        <input type="file" name="fileToUpload" id="fileToUpload"
                        >
                        <span></span>
                    </div>
                </div>

                <div>
                    <label for="summary">Summary:</label>
                    <div>
                        <textarea style="resize: none;" name="summary" rows="2" maxlength="255" required><?php if (isset($restoreInput['summary'])) echo $restoreInput['summary']; ?></textarea>
                        <span></span>
                    </div>
                </div>

                <div>
                    <label for="description">Description:</label>
                    <div>
                        <textarea resizeable name="description" rows="5" required><?php if (isset($restoreInput['description'])) echo $restoreInput['description']; ?></textarea>
                        <span></span>
                    </div>
                </div>

                <input type="hidden" name="university_id" value="<?php echo $_SESSION['user']['university_id']; ?>">

                <input type="submit" name="submit" value="Create RSO">
            </form>
        </main>
    </body>
</html>