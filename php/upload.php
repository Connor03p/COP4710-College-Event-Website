
<?php
    $target_dir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = true;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) 
    {
        $uploadOk = true;
    }
    else 
    {
        echo "File is not an image.";
        $uploadOk = false;
    }

    // Check if file already exists
    $counter = 0;
    while (file_exists($target_file)) 
    {
        $counter += rand(0, 100);
        $target_file = $target_dir . $counter . basename($_FILES["fileToUpload"]["name"]);
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) 
    {
        echo "Sorry, your file is too large.";
        $uploadOk = false;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) 
    {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = false;
    }

    // Check if $uploadOk is set to 0 by an error
    if (!$uploadOk) 
    {
        echo "Sorry, your file was not uploaded.";
        return null;
    } 
    else
    {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
        {
            return array("path" => $target_file, "name" => basename($_FILES["fileToUpload"]["name"]), "type" => $imageFileType, "size" => $_FILES["fileToUpload"]["size"]);
        }
        else
        {
            echo "Sorry, there was an error uploading your file.";
            return null;
        }
    }
?>
