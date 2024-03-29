<?php
    if (isset($_POST["submit"]))
    {
        $new_organization = array(
            "name" => $_POST['name'],
            "university_id" => $_SESSION['user']['university_id'],
            "summary" => $_POST['summary'],
            "description" => $_POST['description']
        );

        mysqli_begin_transaction($conn);

        try
        {
            $query = "INSERT INTO organizations (name, admin_id, university_id, summary, description) VALUES (?, ?, ?, ?, ?)";
            $query = $conn->prepare($query);
            $query->bind_param("siiss", $new_organization['name'], $_SESSION['user']['id'], $new_organization['university_id'], $new_organization['summary'], $new_organization['description']);
            $query->execute();

            mysqli_commit($conn);
            header('location: ' . $dir['domain']);
        }
        catch (Exception $e)
        {
            mysqli_rollback($conn);
            echo $e;
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
            <header>
                <h1>New RSO</h1>
            </header>
            <div class="break-line"></div>

            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Name:</label>
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
                    <div>
                        <textarea style="resize: vertical" name="description" rows="5" required></textarea>
                        <span></span>
                    </div>
                </div>

                <input type="hidden" name="university_id" value="<?php echo $_SESSION['user']['university_id']; ?>">

                <input type="submit" name="submit" value="Create RSO">
            </form>
        </main>
    </body>
</html>