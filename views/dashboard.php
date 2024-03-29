<?php
    require_once $dir['php'] . '\database.php';
    global $conn;

    switch ($_SESSION['user']['role'])
    {
        case 'Super':
            $query = "SELECT * FROM supers WHERE user_id = ?";
            break;
        case 'Admin':
            $query = "SELECT * FROM admins A, university_admins UA WHERE UA.admin_id = A.user_id AND A.user_id = ?";
            break;
        default:
            $query = "SELECT * FROM students S, university_students US WHERE US.student_id = S.user_id AND S.user_id = ?";
            break;
    }
    $query = $conn->prepare($query);
    $query->bind_param("i", $_SESSION['user']['id']);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $_SESSION['user'] = array_merge($_SESSION['user'], $result);
    echo $_SESSION['user']['username'];
    switch ($_SESSION['user']['role'])
    {
        case 'Super':
            if ($_SESSION['user']['hasUniversity'] == 0)
            {
                header('location: ' . $dir['domain']  . '/universities/create');
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
            </header>
            <div class="break-line"></div>

            <a style="text-decoration: none;" href="<?=$dir['domain']?>/events">
                <button>
                    Browse Events
                </button>
            </a>

            <?php if ($_SESSION['user']['role'] == 'Super'): ?>
                <a style="text-decoration: none;" href="<?=$dir['domain']?>/events/create">
                    <button>
                        Create Public Event
                    </button>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] == 'Admin'): ?>
                <a style="text-decoration: none;" href="<?=$dir['domain']?>/events/create">
                    <button>
                        Create RSO Event
                    </button>
                </a>
            <?php endif; ?>

            <a style="text-decoration: none;" href="<?=$dir['domain']?>/organizations">
                <button>
                    Browse RSOs
                </button>
            </a>

            <?php if ($_SESSION['user']['role'] == 'Admin'): ?>
                <a style="text-decoration: none;" href="<?=$dir['domain']?>/organizations/create">
                    <button>
                        Create RSO
                    </button>
                </a>
            <?php endif; ?>

            <?php include $dir['views'] . '\footer.php'; ?>
        </main>


        <!-- Scripts -->
        <script src="js/elements.js"></script>
    </body>
</html>