<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . '\php\database.php';
    global $conn;

    switch ($_SESSION['user']['role'])
    {
        case 'Super':
            $query = "SELECT * FROM supers WHERE user_id = ?";
            break;
        case 'Admin':
            $query = "SELECT * FROM admins WHERE user_id = ?";
            break;
        default:
            $query = "SELECT * FROM students WHERE user_id = ?";
            break;
    }
    $query = $conn->prepare($query);
    $query->bind_param("i", $_SESSION['user']['id']);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $_SESSION['user'] = array_merge($_SESSION['user'], $result);

    switch ($_SESSION['user']['role'])
    {
        case 'Super':
            if ($_SESSION['user']['hasUniversity'] == 0)
            {
                header('location: http://cop4710/universities/create');
            }
            break;
        case 'Admin':
            break;
        default:
            break;
    }
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
                <h1>Welcome, <?php echo $_SESSION['user']['username']; ?></h1>
                <form method="POST">
                    <button name="logout">Logout</button>
                </form>
            </header>
            <div class="break-line"></div>

            <a style="text-decoration: none;" href="http://cop4710/events">
                <button>
                    Browse Events
                </button>
            </a>

            <a style="text-decoration: none;" href="http://cop4710/organizations">
                <button>
                    Browse RSOs
                </button>
            </a>

            <?php include $dir['views'] . '\footer.php'; ?>
        </main>


        <!-- Scripts -->
        <script src="js/elements.js"></script>
    </body>
</html>