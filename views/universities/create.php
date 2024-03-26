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
            $new_organization = array(
                "name" => $_POST['name'],
                "color" => $_POST['color'],
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

                $query = "INSERT INTO universities (name, logo_id, color, description) VALUES (?, ?, ?, ?)";
                $query = $conn->prepare($query);
                $query->bind_param("siss", $new_organization['name'], $file_id, $new_organization['color'], $new_organization['description']);
                $query->execute();

                $university_id = mysqli_insert_id($conn);

                $query = "UPDATE users SET university_id = ? WHERE id = ?";
                $query = $conn->prepare($query);
                $query->bind_param("ii", $university_id, $_SESSION['user']['id']);
                $query->execute();
                $_SESSION['user']['university_id'] = $university_id;

                mysqli_commit($conn);
                header('location: ' . $_SERVER["DOCUMENT_ROOT"] . '\dashboard.php');
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
        <link rel="stylesheet" href="style.css">
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
                    <label for="input-logo">Name</label>
                    <input type="text" name="name" id="input-name" placeholder="University Name" required>
                    <span></span>
                </div>

                <div>
                    <label for="input-logo">Logo</label>
                    <input type="file" name="fileToUpload" id="fileToUpload">
                    <span></span>
                </div>

                <div>
                    <label for="input-logo">Color</label>
                    <input type="color" name="color" id="input-name" placeholder="University Color" required>
                    <span></span>
                </div>

                <div>
                    <label for="input-logo">Description</label>
                    <textarea name="description" rows="5" required></textarea>
                    <span></span>
                </div>

                <input type="submit" name="submit" value="Create University">
            </form>
        </main>
    </body>
</html>