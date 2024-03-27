<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    if (!isset($_SESSION['user']))
    {
        header('location: index.php');   // if not set the user is sendback to login page.
    }

    // Check if url ends with "/create"
    if (substr($_SERVER['REQUEST_URI'], -7) == '/create')
    {
        include $dir['views'] . '/organizations/create.php';
        return;
    }
    
    $rso_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if (isset($rso_id))
    {
        $rso_id = explode("=", $rso_id)[1];
    }
    else
    {
        include $dir['views'] . '/organizations/list.php';
        return;
    }

    require_once $dir['php'] . 'database.php';
    global $conn;

    try
    {
        $sql = "SELECT * FROM organizations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $rso_id);
        $stmt->execute();
        $rso = $stmt->get_result();
        $rso = $rso->fetch_assoc();

        if ($rso['image_id'] != null)
        {
            $sql = "SELECT * FROM files WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $rso['image_id']);
            $stmt->execute();
            $image = $stmt->get_result();
            $image = $image->fetch_assoc();
        }

        if ($rso == null)
        {
            echo 'Event not found.';
        }
        else
        {
            $data = array(
                'id' => $rso['id'],
                'name' => $rso['name'],
                'description' => $rso['description'],
                'image' => $rso['image_id'] != null ? $image : null
            );
            include $dir['views'] . '/organizations/view.php';
        }
    }
    catch (Exception $e)
    {
        echo 'Error: ' . $e->getMessage();
    }

