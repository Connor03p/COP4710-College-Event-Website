<?php
    require_once $dir['php'] . '\database.php';
    global $conn;
    $queryText = [];
    switch ($_SESSION['user']['role'])
    {
        case 'Super':
            $query = "SELECT U.id university_id, S.hasUniversity FROM supers S, universities U WHERE U.super_id = S.user_id AND S.user_id = ?";
            break;
        case 'Admin':
            $query = "SELECT UM.university_id FROM admins A, university_members UM WHERE UM.user_id = A.user_id AND A.user_id = ?";
            break;
        default:
            $query = "SELECT UM.university_id FROM students S, university_members UM WHERE UM.user_id = S.user_id AND S.user_id = ?";
            break;
    }
    $queryText[] = $query;
    $query = $conn->prepare($query);
    $query->bind_param("i", $_SESSION['user']['id']);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    if ($result == null)
    {
        require $dir['views'] . '\error.php';
    }
    else
    {
        $_SESSION['user']['hasUniversity'] = 1;
        $_SESSION['user'] = array_merge($_SESSION['user'], $result);
    }
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

    // Get all RSOs the user is a member of
    $sql = "SELECT O.id, O.name, O.summary, O.description, I.name file_name FROM organizations O LEFT JOIN images I ON I.id = O.image_id, rso_members M WHERE O.id = M.rso_id AND M.user_id = ?";
    $queryText[] = $sql;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user']['id']);
    $stmt->execute();
    $data_rso = $stmt->get_result();
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
            <header style="text-align: center;">
                <h1>Welcome, <?php echo $_SESSION['user']['username']; ?></h1>
            </header>
            <div class="break-line"></div>

            <a class="button" style="text-decoration: none;" href="<?=$dir['domain']?>/events">
                Browse Events
            </a>

            <?php if ($_SESSION['user']['role'] == 'Super'): ?>
                <a class="button" style="text-decoration: none;" href="<?=$dir['domain']?>/events/create">
                    Create Event
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] == 'Admin'): ?>
                <a class="button" style="text-decoration: none;" href="<?=$dir['domain']?>/events/create">
                     Create Private or RSO Event
                </a>
            <?php endif; ?>

            <a class="button" style="text-decoration: none;" href="<?=$dir['domain']?>/organizations">
                Browse RSOs
            </a>

            <a class="button" style="text-decoration: none;" href="<?=$dir['domain']?>/organizations/create">
                Create RSO
            </a>

            <?php if ($data_rso->num_rows > 0): ?>
                <br><br>
                <h3>Your RSOs</h3>
                <?php
                while ($row = $data_rso->fetch_assoc()) {
                    echo "<a style='text-decoration: none;' href='/organizations?id=" . $row['id'] . "'>";
                    echo "    <section class='rso'>";
                    if (isset($row['file_name']) && $row['file_name'] != null)
                    echo "        <img src='" . $dir["uploads"] . $row['file_name'] . "' alt='RSO Logo' class='rso-logo'>";
                    echo "        <div>";
                    echo "          <h3 class='rso-name'>" . $row['name'] . "</h3>";
                    echo "          <div class='rso-description'>" . $row['summary'] . "</div>";
                    echo "        </div>";
                    echo "    </section>";
                    echo "</a>";
                }
                ?>
            <?php endif; ?>

        </main>
