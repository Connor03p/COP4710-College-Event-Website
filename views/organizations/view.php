<!DOCTYPE html>
<?php
    if (isset($_POST['join_member']))
    {
        $sql = "INSERT INTO user_rso (user_id, rso_id, role) VALUES (?, ?, 'Member')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $data['id']);
        $stmt->execute();
    }
    else if (isset($_POST['join_admin']))
    {
        $sql = "INSERT INTO user_rso (user_id, rso_id, role) VALUES (?, ?, 'Admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $data['id']);
        $stmt->execute();
    }
    else if (isset($_POST['leave_rso']))
    {
        $sql = "DELETE FROM user_rso WHERE user_id = ? AND rso_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $data['id']);
        $stmt->execute();
    }

    // Check if user is in RSO
    $sql = "SELECT R.role FROM user_rso R WHERE user_id = ? AND rso_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user']['id'], $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $isInRSO = $result->num_rows > 0;
    $role = $result->fetch_assoc();

    // Get admin of RSO
    $sql = "SELECT U.id, U.username FROM user_rso R, users U WHERE R.user_id = U.id AND R.rso_id = ? AND R.role = 'Admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $hasAdmin = $result->num_rows > 0;
?>

<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RSO: <?=$data['name']?></title>
        <meta name="description" content="">
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <main>
            <header>
                <h1><?=$data['name']?></h1>
                <?php if ($hasAdmin): ?>
                    <p class="center">Managed by: <?=$admin['username']?></p>
                <?php else: ?>
                    <p class="center" style="color: var(--error);">This RSO does not have an admin</p>
                <?php endif; ?>
            </header>
            
            <div class="break-line"></div>
            <?php if (isset($data['image'])): ?>
                <img src="http://cop4710/uploads/<?=$data['image']['name']?>" alt="<?=$data['image']['alt']?>" class="center">
            <?php endif; ?>
            <p class="center"><?=$data['description']?></p>
            <br>
            
            <form method="POST">
                <?php if ($isInRSO): ?>
                    <input type="submit" name="leave_rso" value="Leave RSO">                    
                <?php elseif (!$hasAdmin && $_SESSION['user']['role'] == 'Admin'): ?>
                    <input type="submit" name="join_admin" value="Join as Admin">
                <?php else: ?>
                    <input type="submit" name="join_member" value="Join RSO">
                <?php endif; ?>
            </form>

            <?php include $dir['views'] . 'footer.php'; ?>
        </main>
    </body>
</html>