<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    if (!isset($_SESSION['user']))
    {
        header('location: index.php');   // if not set the user is sendback to login page.
    }

    $university_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if (isset($university_id))
    {
        $university_id = explode("=", $university_id)[1];
    }
    else
    {
        include $dir['views'] . 'super/universities/list.php';
        return;
    }

    if (!isset($_SESSION['user']['university_id']))
    {
        include $dir['views'] . 'super/universities/create.php';
        return;
    }

    require_once $dir['php'] . 'database.php';
    global $conn;

    $sql = "SELECT * FROM universities WHERE id = $university_id";
    $result = $conn->query($sql);
    $university = $result->fetch_assoc();

    if ($university == null)
    {
        echo 'University not found.';
    }
    else
    {
        $data = array(
            'name' => $university['name'],
            'color' => $university['color'],
            'description' => $university['description']
        );
        include $dir['views'] . 'super/universities/view.php';
    }