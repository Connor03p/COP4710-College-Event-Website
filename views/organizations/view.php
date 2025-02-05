<!DOCTYPE html>
<?php
    $organization_id = $_GET['id'];
    $queryText = [];

    if (isset($_POST['join_rso']))
    {
        $sql = "INSERT INTO rso_members (user_id, rso_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $organization_id);
        $stmt->execute();

        unset($_POST);
        header('location: ' . $dir['domain'] . '/organizations?id=' . $organization_id);
        exit();
    }
    else if (isset($_POST['edit_rso']))
    {
        $sql = "UPDATE organizations SET name = ?, summary = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $_POST['name'], $_POST['summary'], $_POST['description'], $organization_id);
        $stmt->execute();

        unset($_POST);
        header('location: ' . $dir['domain'] . '/organizations?id=' . $organization_id);
        exit();
    }
    else if (isset($_POST['leave_rso']))
    {
        $sql = "DELETE FROM rso_members WHERE user_id = ? AND rso_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user']['id'], $organization_id);
        $stmt->execute();

        unset($_POST);
        header('location: ' . $dir['domain'] . '/organizations?id=' . $organization_id);
        exit();
    }

    // Get admin of RSO
    $sql = "SELECT O.name, O.summary, O.description, O.active, U.id admin_id, U.username admin_name FROM organizations O LEFT JOIN users U ON O.admin_id = U.id WHERE O.id = ?";
    $queryText[] = $sql;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organization_id);
    $stmt->execute();
    $data = $stmt->get_result();
    $data = $data->fetch_assoc();
    
    $data['hasAdmin'] = ($data['admin_id'] != null);
    $data['canEdit'] = ($data['admin_id'] == $_SESSION['user']['id']) || $_SESSION['user']['role'] == 'Super';
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
            
            <h1 id="content-name" <?=($data['canEdit'] == true) ? "contenteditable" : "" ?>><?=$data['name']?></h1>
            <?php if ($data['admin_id'] == $_SESSION['user']['id']): ?>
                <p style="color: green;">You are the owner of this RSO</p>
            <?php elseif ($data['hasAdmin']): ?>
                <p>Managed by: <?=$data['admin_name']?></p>
            <?php else: ?>
                <p style="color: red">This RSO does not have an admin</p>
            <?php endif; ?>

            <?php if (!$data['active']): ?>
                <p style="color: red;">This RSO is inactive.</p>
            <?php endif; ?>
            
            <div class="break-line"></div>
            <?php if (isset($data['image'])): ?>
                <img src="<?=$dir['domain']?>/uploads/<?=$data['image']['name']?>" alt="<?=$data['image']['alt']?>" class="center">
            <?php endif; ?>
            <?php if ($data['canEdit'] == true): ?>
                <h3>Summary</h3>
                <div id="content-summary" contenteditable><?=$data['summary']?></div>
            <?php endif; ?>
            <?=($data['canEdit'] == true) ? "<h3>Description</h3>" : "" ?>
            <div id="content-description" <?=($data['canEdit'] == true) ? "contenteditable" : "" ?>><?=$data['description']?></div>
            <br>
            
            <?php 
                $query = "SELECT * FROM rso_members WHERE user_id = ? AND rso_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $_SESSION['user']['id'], $organization_id);
                $stmt->execute();
                $isInRSO = $stmt->get_result()->num_rows > 0;
            ?>
            <form method="POST">
                <?php if ($data['canEdit']): ?>
                    <input id="input-name" type="hidden" name="name" value="<?=$data['name']?>">
                    <input id="input-summary" type="hidden" name="summary" value="<?=$data['summary']?>">
                    <input id="input-description" type="hidden" name="description" value="<?=$data['description']?>">
                    <input id="btn-edit" type="submit" name="edit_rso" value="Save Changes" disabled>
                <?php endif; ?>
                <?php if ($isInRSO): ?>
                    <input type="submit" name="leave_rso" value="Leave RSO">                    
                <?php else: ?>
                    <input type="submit" name="join_rso" value="Join RSO">
                <?php endif; ?>
            </form>

        </main>
    </body>

    <?php if ($data['canEdit']): ?>
        <script>
            const editBtn = document.getElementById('btn-edit');
            const p = document.querySelectorAll('[contenteditable]');
            const description = document.getElementById('content-description');
            var descriptionText = document.getElementById('content-description').innerHTML;
            description.innerHTML = descriptionText.replace(/\n|\r|\r\n/g, '');

            p.forEach((element) => {
                element.addEventListener("focus", function() 
                {
                    if (element.id == "content-description")
                        element.innerText = descriptionText;  
                });

                element.addEventListener("blur", function() 
                {
                    // Update description, removing new line and return characters
                    if (element.id == 'content-description') {
                        descriptionText = element.innerText.replace(/\n+/g, '\n');
                        element.innerHTML = element.innerText.replace(/\n|\r|\r\n/g, '');
                    }

                    // Set form inputs to new values
                    if (element.id == 'content-name') {
                        document.getElementById('input-name').value = element.innerText;
                    }
                    else if (element.id == 'content-summary') {
                        document.getElementById('input-summary').value = element.innerText;
                    }
                    else if (element.id == 'content-description') {
                        document.getElementById('input-description').value = descriptionText;
                    }
                    editBtn.disabled = false;
                });
            });            
        </script>
    <?php endif; ?>
</html>