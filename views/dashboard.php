<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . '\php\database.php';
    global $conn;

    if (isset($_POST['logout']))
    {
        unset($_SESSION["user"]);
        unset($_SESSION["university"]);
        header("Location: http://cop4710/");
    }

    if (!isset($_SESSION['user']))
    {
        header('location: http://cop4710/');   // if not set the user is sendback to login page.
    }

    // Get RSOs the user is in
    $sql = "SELECT rso_id FROM user_rso WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user']['id']);
    $stmt->execute();
    $result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <meta name="description" content="">
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <main>
            <header>
                <?php
                    if (isset($_SESSION['university']['logo'])):
                        $logo = $_SESSION['university']['logo'];
                        if ($logo['type'] == "svg"):
                ?>
                    <div class="logo">
                        <?=$logo['data']?>
                    </div>
                <?php else: ?>
                    <?php echo "<img class='logo' src='data:image/" . $logo['type'] . ";base64," . base64_encode( $logo['data'] ) . "' />"; ?>
                <?php endif; endif; ?>
                
                <h1><?=$_SESSION['university']['name']?></h1>
            </header>
            <div class="break-line"></div>

            <!-- Dashboard Notices -->
            <?php
                // Check if the user is in an RSO                
                if ($result->num_rows > 0)
                {
                    echo "<h3>Your RSOs</h3>";
                    echo "<div id='RSOList'>";
                    while($row = $result->fetch_assoc())
                    {
                        $sql = "SELECT * FROM organizations WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $row['rso_id']);
                        $stmt->execute();
                        $rso = $stmt->get_result();
                        $rso = $rso->fetch_assoc();

                        echo "<a style='text-decoration: none;' href='organizations?id=" . $rso['id'] . "'>";
                        echo "<section id='rso-notice'>";
                        echo "  <h3>" . $rso['name'] . "</h3>";
                        echo "  <p>" . $rso['description'] . "</p>";
                        echo "</section>";
                        echo "</a>";
                        echo "<br>";
                    }
                    echo "</div>";
                }
                else
                {
                    echo "<a style='text-decoration: none;' href='organizations'>";
                    echo "<section id='rso-notice'>";
                    echo "  <h3>You're not in an RSO</h3>";
                    echo "  <p>Join an existing RSO or create your own to get involved with your university community.</p>";
                    echo "</section>";
                    echo "</a>";
                    echo "<br>";
                }
            ?>

            <h3>Today's Events</h3>
            <div id="EventList-Today">                
                <?php
                    $sql = "SELECT * FROM events WHERE university_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $_SESSION['university']['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>

                <?php if ($result->num_rows > 0): ?>
                    <?php 
                        while($row = $result->fetch_assoc())
                        {
                            echo "<details class='event'>";
                            echo "  <summary class='event'>";
                            echo "      <div>";
                            echo "          <h3 class='event-title'>" . $row['title'] . "</h3>";
                            echo "          <p class='event-date'>" . $row['date_start'] . "</p>";
                            echo "      </div>";
                            echo "      <a href='http://" . $_SERVER['HTTP_HOST'] . "/events?id=" . $row['id'] . "'>";
                            echo "          " . file_get_contents($dir['img'] . "arrow-right-solid.svg");
                            echo "      </a>";
                            echo "  </summary>";
                            echo "  <div class='content'>";
                            
                            if (isset($row['link']) && $row['link'] != "")
                                echo "      <a class='event-link' href='" . $row['link'] . "'>" . $row['link'] . "</a>";
                            
                            if (isset($row['description']) && $row['description'] != "")
                                echo "      <p class='event-description'>" . $row['description'] . "</p>";

                            echo "      <button class='event-attend'>Attend</button>";
                            echo "  </div>";
                            echo "</details>";
                        }
                    ?>
                <?php else: ?>
                    <div id="no-events">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="display:block; margin: auto; max-width: 8rem;" fill="var(--highEmphasis)"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM174.6 384.1c-4.5 12.5-18.2 18.9-30.7 14.4s-18.9-18.2-14.4-30.7C146.9 319.4 198.9 288 256 288s109.1 31.4 126.6 79.9c4.5 12.5-2 26.2-14.4 30.7s-26.2-2-30.7-14.4C328.2 358.5 297.2 336 256 336s-72.2 22.5-81.4 48.1zM144.4 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                        <h3 style="text-align: center">No events found</h3>
                    </div>
                <?php endif; ?>
            </div>

            <a style="text-decoration: none; display: flex; justify-content: center;" href="events">
                <button>
                    Browse All Events
                </button>
            </a>

            <?php include $dir['views'] . '\footer.php'; ?>
        </main>


        <!-- Scripts -->
        <script src="js/elements.js"></script>
    </body>
</html>