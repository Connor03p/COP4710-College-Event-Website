<?php
    if (isset($_POST["submit"]))
    {
        if (!isset($_SESSION['user']))
        {
            header('location: http://cop4710/');
        }

        $img_upload = require $_SERVER["DOCUMENT_ROOT"] . '\api\upload.php';

        if (isset($img_upload))
        {
            $new_event = array(
                "name" => $_POST['name'],
                "university_id" => $_SESSION['user']['university_id'],
                "summary" => $_POST['summary'],
                "description" => $_POST['description']
            );

            $new_file = $img_upload;

            mysqli_begin_transaction($conn);

            try
            {
                $query = "INSERT INTO files (name, path, type, size) VALUES (?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("ssss", $new_file['name'], $new_file['path'], $new_file['type'], $new_file['size']);
                $query->execute();

                $file_id = mysqli_insert_id($conn);

                $query = "INSERT INTO organizations (name, university_id, image_id, summary, description) VALUES (?, ?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("siiss", $new_organization['name'], $new_organization['university_id'], $file_id, $new_organization['summary'], $new_organization['description']);
                $query->execute();

                $organization_id = mysqli_insert_id($conn);

                $query = "INSERT INTO user_organizations (user_id, organization_id, role) VALUES (?, ?, 'Admin')";
                $query = $conn->prepare($query);
                $query->bind_param("ii", $_SESSION['user']['id'], $organization_id);
                $query->execute();

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
        <title>Create New RSO</title>
        <meta name="description" content="">
        <link rel="stylesheet" href="../style.css">
    </head>
    
    <body>
        <main>
            <header>
                <h1>New Event</h1>
            </header>
            <div class="break-line"></div>

            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label for="privacy">Privacy:</label>
                    <select id="input-privacy" name="privacy">
                        <option value="public" selected>Public</option>
                        <option value="private">Private</option>
                    </select>
                </div>

                <div>
                    <label for="name">Title:</label>
                    <div>
                        <input type="text" name="name" id="input-name" placeholder="RSO Name" required>
                        <span></span>
                    </div>
                </div>

                <div>
                    <label for="summary">Summary:</label>
                    <div>
                        <textarea style="resize: none;" name="summary" rows="2" maxlength="255" required></textarea>
                        <span></span>
                    </div>
                </div>

                <div>
                    <label for="description">Description:</label>
                    <textarea style="resize: vertical" name="description" rows="5" required></textarea>
                    <span></span>
                </div>

                <input type="submit" name="submit" value="Create Event">
            </form>
        </main>
    </body>
</html>