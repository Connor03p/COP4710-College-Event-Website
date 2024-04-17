<!DOCTYPE html>
<?php
    $user_id = $_GET['id'];
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
            
            <div class="break-line"></div>
            <?php if (isset($data['image'])): ?>
                <img src="<?=$dir['domain']?>/uploads/<?=$data['image']['name']?>" alt="<?=$data['image']['alt']?>" class="center">
            <?php endif; ?>
            <?=($data['canEdit'] == true) ? "<h3>Summary</h3>" : "" ?>
            <div id="content-summary" <?=($data['canEdit'] == true) ? "contenteditable" : "" ?>><?=$data['summary']?></div>
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

            <?php include $dir['views'] . 'footer.php'; ?>
        </main>
    </body>

    <?php if ($data['canEdit']): ?>
        <script>
            const editBtn = document.getElementById('btn-edit');

            const p = document.querySelectorAll('[contenteditable]');

            p.forEach((element) => {
                element.addEventListener("focus", function() 
                {
                    element.textContent = element.innerHTML;                    
                });

                element.addEventListener("blur", function() 
                {
                    element.innerHTML = element.textContent;

                    if (element.id == 'content-name') {
                        document.getElementById('input-name').value = element.innerHTML;
                    }
                    else if (element.id == 'content-summary') {
                        document.getElementById('input-summary').value = element.innerHTML;
                    }
                    else if (element.id == 'content-description') {
                        document.getElementById('input-description').value = element.innerHTML;
                    }

                    editBtn.disabled = false;
                });
            });            
        </script>
    <?php endif; ?>
</html>