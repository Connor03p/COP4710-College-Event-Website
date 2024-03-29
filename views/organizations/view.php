<!DOCTYPE html>
<?php
    $organization_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    $organization_id = explode("=", $organization_id)[1];

    if (isset($_POST['join_student']))
    {
        $sql = "INSERT INTO rso_students (user_id, rso_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $organization_id);
        $stmt->execute();
    }
    else if (isset($_POST['leave_rso']))
    {
        $sql = "DELETE FROM user_rso WHERE user_id = ? AND rso_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $organization_id);
        $stmt->execute();
    }

    // Get admin of RSO
    $sql = "SELECT O.name, O.description, U.id admin_id, U.username admin_name FROM organizations O, users U WHERE O.admin_id = U.id AND O.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organization_id);
    $stmt->execute();
    $data = $stmt->get_result();
    $data = $data->fetch_assoc();
    $data['hasAdmin'] = ($data['admin_id'] != null);
?>

<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RSO: <?=$data['name']?></title>
        <meta name="description" content="">
        <link rel="stylesheet" href="<?=$dir['domain']?>/style.css">
    </head>

    <body>
        <main>
            <header>
                <h1><?=$data['name']?></h1>
                <?php if ($data['hasAdmin']): ?>
                    <p class="center">Managed by: <?=$data['admin_name']?></p>
                <?php else: ?>
                    <p class="center" style="color: var(--error);">This RSO does not have an admin</p>
                <?php endif; ?>
            </header>
            
            <div class="break-line"></div>
            <?php if (isset($data['image'])): ?>
                <img src="<?=$dir['domain']?>/uploads/<?=$data['image']['name']?>" alt="<?=$data['image']['alt']?>" class="center">
            <?php endif; ?>
            <p class="center"><?=$data['description']?></p>
            <br>
            
            <?php if ($_SESSION['user']['role'] == "student"): ?>
                <form method="POST">
                    <?php if ($isInRSO): ?>
                        <input type="submit" name="leave_rso" value="Leave RSO">                    
                    <?php else: ?>
                        <input type="submit" name="join_student" value="Join RSO">
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <?php include $dir['views'] . 'footer.php'; ?>
        </main>
    </body>
</html>