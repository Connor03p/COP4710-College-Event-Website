<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit University Profile</title>
        <meta name="description" content="">
        <link rel="stylesheet" href="style.css">
    </head>
    
    <body>
        <main>
            <?php
                if (isset($_POST["submit"]))
                {
                    include $_SERVER["DOCUMENT_ROOT"] . '\api\universities\create.php';
                }
            ?>
            <header>
                <h1>University Profile</h1>
            </header>
            <div class="break-line"></div>

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